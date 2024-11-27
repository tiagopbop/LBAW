<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function create(Project $project)
    {
        $this->authorize('create', $task);

        return view('tasks.create', compact('project'));
    }

    public function viewTasks(Project $project)
    {
        $tasks = $project->tasks;

        return view('tasks.viewTasks', compact('project', 'tasks'));
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

        Task::create([
            'task_name' => $validated['task_name'],
            'status' => $validated['status'] ?? 'Ongoing',
            'details' => $validated['details'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
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

    public function destroy(Task $task)
{
    if (auth()->id() !== $task->project->members()->wherePivot('role', 'Project owner')->first()->id) {
        abort(403, 'Unauthorized action.');
    }

    $task->delete();

    return back()->with('success', 'Task deleted successfully!');
}

public function edit(Task $task)
{
    $this->authorize('update', $task);

    return view('tasks.edit', compact('task'));
}

public function update(Request $request, Task $task)
{
    $this->authorize('update', $task);

    $validated = $request->validate([
        'task_name' => 'required|string|max:255',
        'status' => 'required|string|in:Ongoing,On-hold,Finished',
        'details' => 'nullable|string|max:500',
        'due_date' => 'nullable|date|after_or_equal:today',
    ]);

    $task->update($validated);

    return redirect()->route('projects.show', $task->project_id)
        ->with('success', 'Task updated successfully!');
}
public function searchTasks(Request $request)
{
    // Get the query string from the request
    $query = $request->input('query');
    $projectId = $request->input('project_id');

    // Query tasks by project_id and filter by task_name
    $tasks = Task::where('project_id', $projectId)
                ->where('task_name', 'like', '%' . $query . '%') // Only tasks with matching names
                ->get(['task_id', 'task_name', 'status', 'due_date']); // Select relevant fields

    // Return tasks as a JSON response
    return response()->json($tasks);
}

}

