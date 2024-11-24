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
        // Validate user input
        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:authenticated_user',
            'email' => 'required|email|max:255|unique:authenticated_user',
            'password' => 'required|min:8|confirmed',
        ]);

        // If validation fails, it will redirect with errors

        AuthenticatedUser::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_creation_date' => now(),
            'suspended_status' => false,  // Default value
            'pfp' => null,  // Default value for profile picture
            'pronouns' => null,  // Default value
            'bio' => null,  // Default value
            'country' => null,  // Default value
        ]);
        
        // Attempt to log in the user
        $credentials = $request->only('email', 'password');
        

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('tests')
                ->withSuccess('You have successfully registered and logged in!');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }
}