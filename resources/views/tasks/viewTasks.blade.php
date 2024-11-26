@extends('layouts.app')

@section('title', 'Tasks')

@section('content')
<div class="container">
    <h1>{{ $project->project_title }}</h1>
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <a href="{{ url()->previous() }}" class="btn btn-success create-project-btn"
    style="padding: 12px 25px; font-size: 18px; border-radius: 5px; text-decoration: none; display: inline-block;">Go Back</a>
    <div class="card mt-4">
        <div class="card-body">
            <h3>Tasks</h3>
            @if($project->tasks->isEmpty())
                <p>No tasks available for this project. You can add one now!</p>
                <a href="{{ route('tasks.create', $project) }}" class="btn btn-success create-project-btn"
                style="padding: 12px 25px; font-size: 18px; border-radius: 5px; text-decoration: none;">Add Task</a>
            @else
                @foreach($project->tasks as $task)
                    <div class="task-box p-2 mt-2">
                        <p><strong>Title:</strong> {{ $task->task_name }}</p>
                        <p><strong>Status:</strong> {{ $task->status }}</p>
                        <p><strong>Due date:</strong> {{ $task->due_date }}</p>
                        <form action="{{ route('tasks.destroy', $task) }}" method="POST" style="display: inline-block; box-shadow: none; outline: none; background-color: #f9f9f9;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this task?')">Delete Task</button>
                        </form>
                    </div>
                @endforeach
                <a href="{{ route('tasks.create', $project) }}" class="btn btn-success create-project-btn"
                style="padding: 12px 25px; font-size: 18px; border-radius: 5px; text-decoration: none;">Add Another Task</a>
            @endif
        </div>
    </div>

</div>
@endsection
