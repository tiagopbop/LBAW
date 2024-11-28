<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserTask;


class TaskController extends Controller
{
    public function create(Project $project)
    {
        $this->authorize('create', $project);

        return view('tasks.create', compact('project'));
    }

    public function viewTasks(Project $project)
    {
        if (Auth::check() && Auth::user()->suspended_status) {
            return redirect()->route('pleading.page')->with('error', 'Your account is suspended. Contact admin for further assistance.');
        }
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
            'assigned_to' => 'nullable|array',
        ]);

        $task = new Task([
            'task_name' => $validated['task_name'],
            'status' => $validated['status'] ?? 'Ongoing',
            'details' => $validated['details'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
            'priority' => $validated['priority'],
        ]);

        $task->project_id = $project->project_id;
        $task->save();

        if ($request->has('assigned_to') && count($request->assigned_to) > 0) {

            $task->users()->attach($request->assigned_to);
        }

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

    public function edit(Task $task, Project $project)
    {
        $this->authorize('update', $task);

        return view('tasks.edit', compact('task', 'project'));
    }

    public function update(Request $request, Task $task, Project $project)
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'task_name' => 'required|string|max:255',
            'status' => 'required|string|in:Ongoing,On-hold,Finished',
            'details' => 'nullable|string|max:500',
            'due_date' => 'nullable|date|after_or_equal:today',
        ]);

        if ($request->has('assigned_to')) {
            $task->users()->sync($request->assigned_to);
        } else {
            $task->users()->detach();
        }

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


    public function show(Task $task)
    {
        $this->authorize('view', $task);

        $task->load('assignedUsers');

        return view('tasks.show', compact('task'));
    }

}