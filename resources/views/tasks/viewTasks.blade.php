@extends('layouts.app')

@section('title', 'Tasks')

@section('content')
<div class="profile-container" style="margin-top: 20px; max-width: 100%;">
    <h1 style="font-weight: bold; color: #027478;">{{ $project->project_title }}</h1>
    @if(session('success'))
        <div>
            {{ session('success') }}
        </div>
    @endif

    <a href="{{ url()->previous() }}" class="large-button" style="display: inline-block;">
        Go Back
    </a>
    <div class="strip">
        <div style="text-align: center;">
            <h1 style="color: #3e56b0;">Tasks</h1>
            @if($project->tasks->isEmpty())
                <p style="text-align: left;">No tasks available for this project. You can add one now!</p>
                <a href="{{ route('tasks.create', $project) }}" class="large-button">Add Task</a>
            @else
                @foreach($project->tasks as $task)
                    <div class="strip" style="text-align: left; margin-top: 20px;">
                        <p style="text-align: left;"><strong>Title:</strong> {{ $task->task_name }}</p>
                        <p style="text-align: left;"><strong>Status:</strong> {{ $task->status }}</p>
                        <p style="text-align: left;"><strong>Due date:</strong> {{ $task->due_date }}</p>
                        <div style="text-align: right;">
                            @if(auth()->id() === $project->members()->wherePivot('role', 'Project owner')->first()?->pivot->user_id || $project->members()->wherePivot('role', 'Manager')->pluck('id')->contains(auth()->id()))
                            <a href="{{ route('tasks.edit', ['project' => $project->project_id, 'task' => $task->task_id]) }}" class="view-project-button" style="background-color: #bfc900;">
                                Edit Task
                            </a>
                            @endif
                            <form action="{{ route('tasks.destroy', $task) }}" method="POST" style="display: inline-flex; box-shadow: none; outline: none;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="delete-button" onclick="return confirm('Are you sure you want to delete this task?')">
                                    Delete Task
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
                <a href="{{ route('tasks.create', $project) }}" class="large-button">
                    Add Another Task
                </a>
            @endif
        </div>
    </div>

</div>
@endsection
