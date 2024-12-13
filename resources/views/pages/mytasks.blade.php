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
                <li>{{ $task->task_name }}</li> <!-- Assuming 'task_name' exists in the 'task' table -->
            @endforeach
        </ul>
    @endif
</div>
@endsection
