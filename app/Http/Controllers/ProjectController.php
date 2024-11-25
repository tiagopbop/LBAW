<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

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
            'project_description' => $validated['project_description'],
            'availability' => $validated['availability'],
            'archived_status' => false, // Default value
            'project_creation_date' => now(), // Default to current date
        ]);
    
        return redirect()->route('projects.show', $project->project_id)
            ->with('success', 'Project created successfully!');
    }

    public function show(Project $project) {
        $project->load('tasks');
        return view('projects.show', compact('project'));
    }
}
