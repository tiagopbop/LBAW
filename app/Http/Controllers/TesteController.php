<?php
namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class TesteController extends Controller
{
    /**
     * Show the logged-in user's username, email and all projects.
     */
    public function showUserDetails(): View
    {
        // Get the currently authenticated user.
        $user = Auth::user(); // This retrieves the logged-in user (AuthenticatedUser model)
        // Get all projects to display by default.
        $projects = Project::select('project_title', 'project_description')->get();
        // Return the view with user details (username and email).
        return view('pages.tests', [
            'username' => $user->username,
            'email' => $user->email,
            'projects' => $projects,
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


    public function searchProjects(Request $request): JsonResponse
    {
        $searchTerm = $request->input('query'); // Get the search term from the request
    
        if ($searchTerm) {
            // Use the full-text search to filter projects
            $projects = Project::select('project_title', 'project_description')
                ->whereRaw("ts_vector_title_description @@ plainto_tsquery('portuguese', ?)", [$searchTerm])
                ->get();
        } else {
            // If no search term, return all projects
            $projects = Project::select('project_title', 'project_description')->get();
        }
    
        return response()->json($projects);
    }


}
