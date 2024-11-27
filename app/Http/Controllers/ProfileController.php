<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Display the profile page for the currently authenticated user.
     */
    public function show()
    {
        $user = Auth::user();
        if ($user->suspended_status) {
            return redirect()->route('pleading.page')->with('error', 'Your account is suspended. Contact admin for further assistance.');
        }
        return view('pages.profile', [
            'username' => $user->username,
            'email' => $user->email,
            'pfp' => $user->pfp,
            'pronouns' => $user->pronouns,
            'country' => $user->country,
            'bio' => $user->bio,
        ]);
    }

    /**
     * Update the profile of the currently authenticated user.
     */
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
            // Delete the old profile picture if it exists and isn't the default
            if ($user->pfp && $user->pfp !== 'profile_pictures/default-profile.jpg') {
                Storage::disk('public')->delete($user->pfp);
            }

            // Save the new profile picture
            $path = $request->file('pfp')->store('profile_pictures', 'public');
            $user->pfp = $path; // Save the path to the database
        }


        // Update other fields
        $user->pronouns = $request->input('pronouns');
        $user->bio = $request->input('bio');
        $user->country = $request->input('country');

        $user->save();

        return redirect()->route('profile.show')->with('success', 'Profile updated successfully!');
    }


    public function removeImage(Request $request)
    {
        $user = Auth::user();

        // Delete the current profile picture file if it exists and is not the default
        if ($user->pfp && $user->pfp !== 'profile_pictures/default-profile.jpg') {
            Storage::disk('public')->delete($user->pfp);
        }

        // Set the profile picture to the default
        $user->pfp = 'profile_pictures/default-profile.jpg';
        $user->save();

        return redirect()->route('profile.show')->with('success', 'Profile image removed successfully.');
    }

}
