@extends('layouts.app')

@section('content')
    <h1>{{ $project->project_title }}</h1>
    <p><strong>Description:</strong> {{ $project->project_description }}</p>
    <p><strong>Availability:</strong> {{ $project->availability ? 'Available' : 'Unavailable' }}</p>
    <p><strong>Created At:</strong> {{ $project->project_creation_date }}</p>
    <p><strong>Archived:</strong> {{ $project->archived_status ? 'Yes' : 'No' }}</p>

    <h2>Members</h2>
    @foreach ($project->members as $member)
        <p>{{ $member->username }} - {{ $member->pivot->role }}</p>
    @endforeach

    <h2>Tasks</h2>
    @if ($project->tasks && $project->tasks->isNotEmpty())
        @foreach ($project->tasks as $task)
            <p>{{ $task->task_name }} ({{ $task->status }})</p>
        @endforeach
    @else
        <p>No tasks available for this project. You can add one now!</p>
    @endif

    <a href="{{ route('tasks.create', $project->project_id) }}">Add Task</a>
@endsection
