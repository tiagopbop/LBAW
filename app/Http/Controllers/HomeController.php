<?php
namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class HomeController extends Controller
{
    /**
     * Show the logged-in user's username, email and all projects.
     */
    public function showUserDetails(): View
    {
        $user = Auth::user();
        $projects = Project::public()->select('project_title', 'project_description')->get(); //get only public projects
        return view('pages.home', [
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
        Auth::logout();
        return redirect('/login');
    }


    public function searchProjects(Request $request): JsonResponse
    {
        $searchTerm = $request->input('query');
    
        if ($searchTerm) {
            // Use the full-text search to search for public projects
            $projects = Project::public()
                ->select('project_title', 'project_description')
                ->whereRaw("ts_vector_title_description @@ plainto_tsquery('english', ?)", [$searchTerm])
                ->get();
        } else {
            // If no search term, return all projects
            $projects = Project::public()->select('project_title', 'project_description')->get();
        }
    
        return response()->json($projects);
    }


}
