<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TesteController extends Controller
{
    /**
     * Show the logged-in user's username and email.
     */
    public function showUserDetails(): View
    {
        // Get the currently authenticated user.
        $user = Auth::user(); // This retrieves the logged-in user (AuthenticatedUser model)

        // Return the view with user details (username and email).
        return view('pages.tests', [
            'username' => $user->username,
            'email' => $user->email
        ]);
    }

    /**
     * Logout the current user.
     */
    public function logout()
    {
        Auth::logout(); // Logs out the current user
        return redirect('/login'); // Redirect to login page after logout
    }
}
