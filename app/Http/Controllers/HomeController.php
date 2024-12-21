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
    $filters = $request->input('filters', []); // Get filters as an array

    // Query only public projects
    $projects = Project::public()
        ->select('project_id', 'project_title', 'project_description');

    if ($searchTerm) {
        // Apply full-text search for the search term
        $projects->whereRaw("ts_vector_title_description @@ plainto_tsquery('english', ?)", [$searchTerm]);
    }

    if (!empty($filters)) {
        // Apply multiple filters dynamically with AND logic
        foreach ($filters as $filter) {
            $projects->where(function ($query) use ($filter) {
                $query->where('project_title', 'ILIKE', "%{$filter}%")
                      ->orWhere('project_description', 'ILIKE', "%{$filter}%");
            });
        }
    }

    // Fetch the results
    $results = $projects->get();

    return response()->json($results);
}


}
