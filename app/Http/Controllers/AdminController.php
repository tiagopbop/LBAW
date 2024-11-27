<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;
use App\Models\AuthenticatedUser;

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

            return redirect()->route('admin.dashboard');
        }

        return back()->withErrors(['admin_tag' => 'Invalid credentials.']);
    }
    public function dashboard()
    {
        $users = AuthenticatedUser::all();

        return view('admin.dashboard', compact('users'));
    }

    public function logout()
    {
        Auth::guard('admin')->logout();
        return redirect()->route('login');
    }
}
