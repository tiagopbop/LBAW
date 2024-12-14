@extends('layouts.app')

@section('title', 'My Tasks')

@section('content')
<div class="strip profile-container" style="margin-top: 20px; max-width: 100%;"> 
    <h1 style="font-weight: bold; color: #027478;">My Tasks</h1>
</div>
<div class="strip">    
    @if($tasks->isEmpty())
        <p>No tasks assigned to you yet.</p>
    @else
        <ul>
            @foreach ($tasks as $task)
                <div class="strip" data-id="{{ $task->task_id }}" >
                            <p style="font-size: 18px;"><strong>Project:</strong> {{ $task->project_title }}</p>
                            <p><strong>Title:</strong> {{ $task->task_name }}</p>
                            <p><strong>Status:</strong> {{ $task->status }}</p>
                            <p><strong>Due date:</strong> {{ $task->due_date }}</p>
                            
                </div>            
            @endforeach
        </ul>
        
    @endif
</div>
@endsection
