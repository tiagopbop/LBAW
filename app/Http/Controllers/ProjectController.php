<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    public function create()
    {
        return view('projects.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_title' => 'required|string|max:50',
            'project_description' => 'nullable|string|max:500',
            'availability' => 'required|boolean',
        ]);

        $project = Project::create([
            'project_title' => $validated['project_title'],
            'project_description' => $validated['project_description'] ?? 'No description',
            'availability' => $validated['availability'],
            'archived_status' => false,
        ]);

        $project->members()->attach(Auth::id(), ['role' => 'Project owner']);

        return redirect()->route('projects.myProjects');
    }

    public function show(Project $project) {
        $project->load(['tasks', 'members']);
        return view('projects.show', compact('project'));
    }

    public function myProjects()
    {
        $projects = auth()->user()->projects;

        return view('projects.myProjects', compact('projects'));
    }

    public function destroy(Project $project)
{
    if (auth()->id() !== $project->members()->wherePivot('role', 'Project owner')->first()->id) {
        abort(403, 'Unauthorized action.');
    }

    $project->delete();

    return redirect()->route('projects.myProjects')->with('success', 'Project deleted successfully!');
}

}
