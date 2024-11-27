@extends('layouts.app')

@section('title', 'Tasks')

@section('content')
<div>
    <h1>{{ $project->project_title }}</h1>
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
            <h3 style="text-align: left;">Tasks</h3>
            @if($project->tasks->isEmpty())
                <p style="text-align: left;">No tasks available for this project. You can add one now!</p>
                <a href="{{ route('tasks.create', $project) }}" class="large-button">Add Task</a>
            @else
                @foreach($project->tasks as $task)
                    <div class="strip" style="text-align: left; margin-top: 20px;">
                        <p><strong>Title:</strong> {{ $task->task_name }}</p>
                        <p><strong>Status:</strong> {{ $task->status }}</p>
                        <p><strong>Due date:</strong> {{ $task->due_date }}</p>
                        <div style="text-align: right;">
                            <form action="{{ route('tasks.destroy', $task) }}" method="POST" style="display: inline-flex; box-shadow: none; outline: none; background-color: #f4f6f8;">
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
