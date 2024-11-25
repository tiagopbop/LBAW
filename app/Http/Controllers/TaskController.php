<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function create(Project $project)
    {
        return view('tasks.create', compact('project'));
    }

    public function store(Request $request, Project $project)
    {
        $validated = $request->validate([
            'task_name' => 'required|string|max:255',
            'status' => 'required|string|in:Ongoing,On-hold,Finished',
            'details' => 'nullable|string|max:500',
            'due_date' => 'nullable|date|after_or_equal:today',
            'priority' => 'required|string|in:High,Medium,Low',
        ]);

        if (!$project->exists) {
            return redirect()->route('projects.index')->with('error', 'Project not found.');
        }

        $task = new Task($validated);
        $task->project_id = $project->project_id;
        $task->save();

        return redirect()->route('projects.show', $project->project_id)
            ->with('success', 'Task added successfully!');
    }
}

