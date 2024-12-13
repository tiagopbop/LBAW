@extends('layouts.app')

@section('title', 'My Tasks')

@section('content')
<div class="strip profile-container" style="margin-top: 20px; max-width: 100%;">
    <h1 style="font-weight: bold; color: #027478;">My Tasks</h1>
    @if($tasks->isEmpty())
        <p>No tasks assigned to you yet.</p>
    @else
        <ul>
            @foreach ($tasks as $task)
                <div class="task" data-id="{{ $task->task_id }}" style="text-align: left; margin-top: 20px;">
                            <p><strong>Title:</strong> {{ $task->task_name }}</p>
                            <p><strong>Status:</strong> {{ $task->status }}</p>
                            <p><strong>Due date:</strong> {{ $task->due_date }}</p>
                            <p><strong>Assigned To:</strong>
                                @if($task->assignedUsers && $task->assignedUsers->isNotEmpty())
                                    {{ $task->assignedUsers->pluck('username')->join(', ') }}
                                @else
                                    Not assigned
                                @endif
                            </p>
            @endforeach
        </ul>
        
    @endif
</div>
@endsection
