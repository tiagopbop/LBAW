@extends('layouts.app')

@section('content')
    <div class="profile-container">
        <h1>Edit Task</h1>
        <!-- Use the correct route structure with both project and task ids -->
        <form action="{{ route('tasks.update', [$project, $task]) }}" method="POST">
            @csrf
            @method('PUT')

            <label for="task_name">Task Name</label>
            <input type="text" id="task_name" name="task_name" value="{{ $task->task_name }}" required>

            <label for="status">Status</label>
            <select id="status" name="status" required>
                <option value="Ongoing" {{ $task->status === 'Ongoing' ? 'selected' : '' }}>Ongoing</option>
                <option value="On-hold" {{ $task->status === 'On-hold' ? 'selected' : '' }}>On-hold</option>
                <option value="Finished" {{ $task->status === 'Finished' ? 'selected' : '' }}>Finished</option>
            </select>

            <div>
                <label for="assigned_to">Assign To:</label>
                <div>
                    @foreach ($project->members as $member)
                        <label>
                            <input type="checkbox" name="assigned_to[]" value="{{ $member->id }}"
                                   @if($task->users->contains($member->id)) checked @endif>
                            {{ $member->username }} - {{ ucfirst($member->pivot->role ?? 'Member') }}
                        </label>
                        <br>
                    @endforeach
                </div>
            </div>

            <label for="details">Details</label>
            <textarea id="details" name="details">{{ $task->details }}</textarea>

            <label for="due_date">Due Date</label>
            <input type="date" id="due_date" name="due_date" value="{{ $task->due_date }}">

            <label for="priority">Priority</label>
            <select id="priority" name="priority" required>
                <option value="High" {{ $task->priority === 'High' ? 'selected' : '' }}>High</option>
                <option value="Medium" {{ $task->priority === 'Medium' ? 'selected' : '' }}>Medium</option>
                <option value="Low" {{ $task->priority === 'Low' ? 'selected' : '' }}>Low</option>
            </select>

            <button type="submit">Update Task</button>
        </form>
    </div>
@endsection
