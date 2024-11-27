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
                            <div style="text-align: right;">
                                @if(auth()->id() === $project->members()->wherePivot('role', 'Project owner')->first()->id || $project->members()->wherePivot('role', 'Manager')->pluck('id')->contains(auth()->id()))
                                    <a href="{{ route('tasks.edit', ['project' => $project->project_id, 'task' => $task]) }}" class="view-project-button" style="background-color: #bfc900;">
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
                @endif
            </div>
            
            <a href="{{ route('tasks.create', $project) }}" class="large-button" style="margin-top: 20px;">
                Add Task
            </a>

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
                            <p><strong>Title:</strong> ${task.task_name}</p>
                            <p><strong>Status:</strong> ${task.status}</p>
                            <p><strong>Due date:</strong> ${task.due_date}</p>
                            <div style="text-align: right;">
                                <a href="/tasks/${task.task_id}/edit" class="view-project-button" style="background-color: #bfc900;">Edit Task</a>
                                <form action="/tasks/${task.task_id}" method="POST" style="display: inline-flex;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="delete-button" onclick="return confirm('Are you sure you want to delete this task?')">Delete Task</button>
                                </form>
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