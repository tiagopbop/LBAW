@extends('layouts.app')

@section('content')
<div class="profile-container">
    <h1>
        Add a Task to {{ $project->project_title }}
    </h1>

    @can('create', $project)
    <form id="edit-profile-form" action="{{ route('tasks.store', $project->project_id) }}" method="POST">
        @csrf
        <div>
            <label for="task_name">Task Name:</label>
            <input type="text" id="task_name" name="task_name" required maxlength="255">
        </div>
        <div>
            <label for="status">Status:</label>
            <select id="status" name="status" style="resize: none; width: 25%; height: 1.4cm; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; margin-bottom: 15px; box-sizing: border-box;" required>
                <option value="Ongoing">Ongoing</option>
                <option value="On-hold">On-hold</option>
                <option value="Finished">Finished</option>
            </select>
        </div>
        <div>
            <label for="details">Details:</label>
            <textarea id="details" name="details" maxlength="500" style="resize: none; width: 100%; height: 2cm; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; margin-bottom: 15px; box-sizing: border-box;"></textarea>
        </div>
        <div>
            <label for="due_date">Due Date:</label>
            <input type="date" id="due_date" name="due_date" style="resize: none; width: 34%; height: 1.4cm; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; margin-bottom: 15px; box-sizing: border-box;">
        </div>
        <div>
            <label for="priority">Priority:</label>
            <select id="priority" name="priority" required style="resize: none; width: 25%; height: 1.4cm; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; margin-bottom: 15px; box-sizing: border-box;">
                <option value="High">High</option>
                <option value="Medium">Medium</option>
                <option value="Low">Low</option>
            </select>
        </div>
        <button type="submit">Add Task</button>
    </form>
    @else
    <p>You do not have permission to add tasks to this project.</p>
    @endcan
</div>
@endsection
