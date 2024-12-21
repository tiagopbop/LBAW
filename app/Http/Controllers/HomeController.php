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
        $projects = Project::public()->select('project_id', 'project_title', 'project_description')->get(); //get only public projects
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

    public function pleading()
    {
        return view('pages.pleading');
    }

    public function searchProjects(Request $request): JsonResponse
{
    $searchTerm = $request->input('query');
    $filter = $request->input('filter'); // New filter input

    $projects = Project::public()
        ->select('project_id', 'project_title', 'project_description');

    if ($searchTerm) {
        // Use full-text search for the search term
        $projects->whereRaw("ts_vector_title_description @@ plainto_tsquery('english', ?)", [$searchTerm]);
    }

    if ($filter) {
        // Apply additional filtering by checking if the filter keyword exists in the title or description
        $projects->where(function ($query) use ($filter) {
            $query->where('project_title', 'ILIKE', "%{$filter}%")
                  ->orWhere('project_description', 'ILIKE', "%{$filter}%");
        });
    }

    return response()->json($projects->get());
}

}
