<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\AuthenticatedUser;
use App\Models\Follow;

class ProfileController extends Controller
{
    public function show($username)
    {
        $user = AuthenticatedUser::where('username', $username)->firstOrFail();

        // Only redirect to pleading page if the authenticated user is viewing their own suspended profile
        if ($user->suspended_status && Auth::check() && Auth::id() === $user->id) {
            return redirect()->route('pleading.page')->with('error', 'Your account is suspended. Contact admin for further assistance.');
        }

        return view('pages.profile', [
            'user' => $user,
            'username' => $user->username,
            'email' => $user->email,
            'pfp' => $user->pfp,
            'pronouns' => $user->pronouns,
            'country' => $user->country,
            'bio' => $user->bio,
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'pfp' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'pronouns' => 'nullable|string|max:50',
            'bio' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:100',
        ]);

        if ($request->hasFile('pfp')) {
            if ($user->pfp && $user->pfp !== 'profile_pictures/default-profile.jpg') {
                Storage::disk('public')->delete($user->pfp);
            }

            $path = $request->file('pfp')->store('profile_pictures', 'public');
            $user->pfp = $path;
        }

        $user->pronouns = $request->input('pronouns');
        $user->bio = $request->input('bio');
        $user->country = $request->input('country');

        $user->save();

        return redirect()->route('profile.show', $user->username)->with('success', 'Profile updated successfully!');
    }

    public function removeImage(Request $request)
    {
        $user = Auth::user();

        if ($user->pfp && $user->pfp !== 'profile_pictures/default-profile.jpg') {
            Storage::disk('public')->delete($user->pfp);
        }

        $user->pfp = 'profile_pictures/default-profile.jpg';
        $user->save();

        return redirect()->route('profile.show', $user->username)->with('success', 'Profile image removed successfully.');
    }

    public function deleteAccount(Request $request)
    {
        $user = Auth::user();

        if ($user->id !== Auth::id()) {
            return redirect()->route('profile.show', $user->username)->withErrors(['error' => 'You can only delete your own account.']);
        }

        $user->delete();
        Auth::logout();
        return redirect('/')->with('status', 'Your account has been deleted successfully.');
    }

    public function follow($username)
    {
        $userToFollow = AuthenticatedUser::where('username', $username)->firstOrFail();
        $follower = Auth::user();

        if ($follower->id !== $userToFollow->id && !$follower->following()->where('followed_id', $userToFollow->id)->exists()) {
            Follow::create([
                'follower_id' => $follower->id,
                'followed_id' => $userToFollow->id,
            ]);
        }

        return redirect()->route('profile.show', $username)->with('success', 'User followed successfully!');
    }

    public function unfollow($username)
    {
        $userToUnfollow = AuthenticatedUser::where('username', $username)->firstOrFail();
        $follower = Auth::user();

        $follow = $follower->following()->where('followed_id', $userToUnfollow->id)->first();
        if ($follow) {
            $follow->delete();
        }

        return redirect()->route('profile.show', $username)->with('success', 'User unfollowed successfully!');
    }

    public function followers($username)
    {
        $user = AuthenticatedUser::where('username', $username)->firstOrFail();
        $followers = $user->followers()->with('follower')->get();

        return view('pages.followers', compact('user', 'followers'));
    }

    public function following($username)
    {
        $user = AuthenticatedUser::where('username', $username)->firstOrFail();
        $following = $user->following()->with('followed')->get();

        return view('pages.following', compact('user', 'following'));
    }
}