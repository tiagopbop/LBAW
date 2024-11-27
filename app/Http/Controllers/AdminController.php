<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;
use App\Models\AuthenticatedUser;
use App\Models\Plea;


class AdminController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.supersecretlogin');
    }

    public function login(Request $request)
    {
        $request->validate([
            'admin_tag' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $admin = Admin::where('admin_tag', $request->admin_tag)->first();

        if ($admin && Hash::check($request->password, $admin->password)) {
            session(['admin_id' => $admin->admin_id]);

            return redirect()->route('admin.unsuspended_users');
        }

        return back()->withErrors(['admin_tag' => 'Invalid credentials.']);
    }

    public function unsuspendedUsers()
    {
        $users = AuthenticatedUser::where('suspended_status', false)->get();
        return view('admin.unsuspended_users', compact('users'));
    }

    public function suspendedUsers()
    {
        $users = AuthenticatedUser::where('suspended_status', true)->get();
        return view('admin.suspended_users', compact('users'));
    }

    public function pleasDashboard()
    {
        $pleas = Plea::with('user')->get();
        return view('admin.pleas_dashboard', compact('pleas'));
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.loginForm');
    }

    public function toggleSuspend(Request $request, $id)
    {
        $user = AuthenticatedUser::findOrFail($id);

        $user->suspended_status = !$user->suspended_status;
        $user->save();

        return redirect()->route('admin.unsuspended_users')->with('success', 'User suspension status updated successfully.');
    }


    public function unsuspendUser($id)
    {
        $user = AuthenticatedUser::findOrFail($id);
        $user->suspended_status = false;
        $user->save();

        return redirect()->route('admin.suspended_users')->with('success', 'User unsuspended successfully.');
    }
}