@extends('layouts.app')

@section('content')
    <h1>Add a Task to {{ $project->project_title }}</h1>

    <form action="{{ route('tasks.store', $project->project_id) }}" method="POST">
        @csrf
        <div>
            <label for="task_name">Task Name:</label>
            <input type="text" id="task_name" name="task_name" required maxlength="255">
        </div>
        <div>
            <label for="status">Status:</label>
            <select id="status" name="status" required>
                <option value="Ongoing">Ongoing</option>
                <option value="On-hold">On-hold</option>
                <option value="Finished">Finished</option>
            </select>
        </div>
        <div>
            <label for="details">Details:</label>
            <textarea id="details" name="details" maxlength="500"></textarea>
        </div>
        <div>
            <label for="due_date">Due Date:</label>
            <input type="date" id="due_date" name="due_date">
        </div>
        <div>
            <label for="priority">Priority:</label>
            <select id="priority" name="priority" required>
                <option value="High">High</option>
                <option value="Medium">Medium</option>
                <option value="Low">Low</option>
            </select>
        </div>
        <button type="submit">Add Task</button>
    </form>
@endsection
