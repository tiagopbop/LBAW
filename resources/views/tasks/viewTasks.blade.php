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
        <?php
        $isManagerOrOwner = $project->members
            ->where('id', auth()->id())
            ->whereIn('pivot.role', ['Project owner', 'Manager'])
            ->isNotEmpty();
        ?>

        <a href="{{ url()->previous() }}" class="large-button" style="display: inline-block;">
            Go Back
        </a>

        <div class="strip">
            <div style="text-align: center;">
                <h1 style="color: #3e56b0;">Tasks</h1>

                <input type="text" id="task-search" placeholder="Search tasks by title..." style="margin-bottom: 20px; padding: 10px; width: 300px;">

                <div id="task-list">
                    @if($project->tasks->isEmpty())
                        <p style="text-align: left;">No tasks available for this project. You can add one now!</p>
                    @else
                        @foreach($project->tasks as $task)
                            <div class="task" data-id="{{ $task->task_id }}" style="text-align: left; margin-top: 20px;">
                                <p><strong>Title:</strong> {{ $task->task_name }}</p>
                                <p><strong>Status:</strong> {{ $task->status }}</p>
                                <p><strong>Due date:</strong> {{ $task->due_date }}</p>
                                <p><strong>Details:</strong> {{ $task->details }}</p>
                                <p><strong>Assigned To:</strong>
                                        <?php
                                        $assignedUsers = $task->assignedUsers ? $task->assignedUsers->pluck('username')->toArray() : [];
                                        echo !empty($assignedUsers) ? implode(', ', $assignedUsers) : 'Not assigned';
                                        ?>
                                </p>
                                <div style="text-align: right;">
                                        <?php if ($isManagerOrOwner): ?>
                                    <a href="{{ route('tasks.edit', ['project' => $project->project_id, 'task' => $task]) }}" class="view-project-button" style="background-color: #bfc900;">
                                        Edit Task
                                    </a>

                                    <form action="{{ route('tasks.destroy', $task) }}" method="POST" style="display: inline-flex; box-shadow: none; outline: none;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="delete-button" onclick="return confirm('Are you sure you want to delete this task?')">
                                            Delete Task
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
                <?php if ($isManagerOrOwner): ?>
                <a href="{{ route('tasks.create', $project) }}" class="large-button" style="margin-top: 20px;">
                    Add Task
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

@endsection


@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('task-search');
            const taskList = document.getElementById('task-list');

            // Fetch tasks based on search query
            function fetchTasks(query = '') {
                fetch(`/tasks/search?query=${encodeURIComponent(query)}&project_id={{ $project->project_id }}`)
                    .then(response => response.json())
                    .then(data => {
                        taskList.innerHTML = ''; // Clear current task list

                        if (data.length === 0) {
                            taskList.innerHTML = '<p>No tasks found matching the search criteria.</p>';
                        } else {
                            // Dynamically generate task list from response
                            data.forEach(task => {
                                const taskDiv = document.createElement('div');
                                taskDiv.classList.add('task');
                                taskDiv.setAttribute('data-id', task.task_id);
                                taskDiv.innerHTML = `
                            <div class="strip">
                                <p style="text-align: left;"><strong>Title:</strong> ${task.task_name}</p>
                                <p style="text-align: left;"><strong>Status:</strong> ${task.status}</p>
                                <p style="text-align: left;"><strong>Due date:</strong> ${task.due_date}</p>
                                <p style="text-align: left;"><strong>Details:</strong> ${task.details}</p>
                                <p><strong>Assigned To:</strong> ${
                                    task.assignedUsers && task.assignedUsers.length > 0
                                        ? task.assignedUsers.map(user => user.username).join(', ')
                                        : 'Not assigned'
                                }</p>
                                    <div style="text-align: right;">
                                    <?php if ($isManagerOrOwner): ?>
                                <a href="/tasks/${task.task_id}/edit" class="view-project-button" style="background-color: #bfc900;">Edit Task</a>
                                        <form action="/tasks/${task.task_id}" method="POST" style="display: inline-flex; border: none; box-shadow: none;">
                                            @csrf
                                @method('DELETE')
                                <button type="submit" class="delete-button" onclick="return confirm('Are you sure you want to delete this task?')">Delete Task</button>
                                </form>
<?php endif; ?>
                                </div>
                            </div>

`;
                                taskList.appendChild(taskDiv);
                            });
                        }
                    })
                    .catch(error => console.error('Error fetching tasks:', error));
            }

            // Listen for search input and trigger task filtering
            searchInput.addEventListener('input', function () {
                const query = searchInput.value.trim();
                fetchTasks(query); // Fetch tasks based on the input query
            });

            // Initial load of all tasks when page is first loaded
            fetchTasks();
        });
    </script>
@endpush

message.txt
8 KB