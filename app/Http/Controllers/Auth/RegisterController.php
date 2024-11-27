<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use Illuminate\View\View;

use App\Models\AuthenticatedUser;

class RegisterController extends Controller
{
    /**
     * Display a registration form.
     */
    public function showRegistrationForm(): View
    {
        return view('auth.register');
    }

    /**
     * Register a new user.
     */
    public function register(Request $request)
    {   
        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:authenticated_user',
            'email' => 'required|email|max:255|unique:authenticated_user',
            'password' => 'required|min:8|confirmed',
        ]);


        AuthenticatedUser::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_creation_date' => now(),
            'suspended_status' => false,
            'pfp' => null,
            'pronouns' => null,
            'bio' => null,
            'country' => null,
        ]);
        
        // Attempt to log in the user
        $credentials = $request->only('email', 'password');
        

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('home')
                ->withSuccess('You have successfully registered and logged in!');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }
}