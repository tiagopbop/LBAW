@extends('layouts.app')

@section('content')
<div class="profile-container">
    <h1>
        Edit Task
    </h1>
    <form id="edit-profile-form" action="{{ route('tasks.update', $task) }}" method="POST">
        @csrf
        @method('PUT')
        
        <label for="task_name">Task Name</label>
        <input type="text" id="task_name" name="task_name" class="strip" value="{{ $task->task_name }}" required>

        <label for="status">Status</label>
        <select id="status" name="status" class="strip">
            <option value="Ongoing" {{ $task->status === 'Ongoing' ? 'selected' : '' }}>Ongoing</option>
            <option value="On-hold" {{ $task->status === 'On-hold' ? 'selected' : '' }}>On-hold</option>
            <option value="Finished" {{ $task->status === 'Finished' ? 'selected' : '' }}>Finished</option>
        </select>
        <div class="form-group">
            <label for="assigned_to">Assign To:</label>
            <select name="assigned_to[]" id="assigned_to" multiple size="5" style="width: 100%; padding: 5px;">
                @foreach ($project->members as $member)
                    <option value="{{ $member->id }}">
                        {{ $member->username ?? 'Unknown' }} - {{ ucfirst($member->pivot->role ?? 'Member') }}
                    </option>
                @endforeach
            </select>
        </div>
        <label for="details">Details</label>
        <textarea id="details" name="details" class="strip" style="max-width: 95%;" rows="4">{{ $task->details }}</textarea>

        <label for="due_date">Due Date</label>
        <input type="date" id="due_date" name="due_date" class="strip" style="max-width: 95%;" value="{{ $task->due_date }}">

        <button type="submit" class="large-button">Update Task</button>
    </form>
</div>
@endsection