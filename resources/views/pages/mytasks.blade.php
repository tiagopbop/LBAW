@extends('layouts.app')

@section('title', 'My Tasks')

@section('content')
    <div class="strip profile-container" style="margin-top: 20px; max-width: 100%;">
        <h1 style="font-weight: bold; color: #027478;">My Tasks</h1>
    </div>

    <div class="strip" style="margin-bottom: 20px;">
        <!-- Status filter buttons -->
        <button class="status-button" data-status="Ongoing" onclick="filterTasks('Ongoing')">Ongoing</button>
        <button class="status-button" data-status="On-hold" onclick="filterTasks('On-hold')">On Hold</button>
        <button class="status-button" data-status="Finished" onclick="filterTasks('Finished')">Finished</button>
    </div>

    <div class="strip">
        @if($tasks->isEmpty())
            <p>No tasks assigned to you yet.</p>
        @else
            <ul id="task-list">
                @foreach ($tasks as $task)
                    <li class="task-item" data-status="{{ $task->status }}" data-id="{{ $task->task_id }}">
                        <div class="strip">
                            <p style="font-size: 18px;"><strong>Project:</strong> {{ $task->project_title }}</p>
                            <p><strong>Title:</strong> {{ $task->task_name }}</p>
                            <p><strong>Status:</strong> <span class="task-status" data-id="{{ $task->task_id }}">{{ $task->status }}</span></p>
                            <p><strong>Due date:</strong> {{ $task->due_date }}</p>

                            <!-- Buttons to change the task status -->
                            <button class="status-btn" data-status="Ongoing" onclick="updateTaskStatus({{ $task->task_id }}, 'Ongoing')">Ongoing</button>
                            <button class="status-btn" data-status="On-hold" onclick="updateTaskStatus({{ $task->task_id }}, 'On-hold')">On Hold</button>
                            <button class="status-btn" data-status="Finished" onclick="updateTaskStatus({{ $task->task_id }}, 'Finished')">Finished</button>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

@endsection

@push('scripts')
    <script>
        // Function to filter tasks by status
        function filterTasks(status) {
            const tasks = document.querySelectorAll('.task-item');
            const buttons = document.querySelectorAll('.status-button');

            // Check if the clicked button is already active (i.e., filter is already applied)
            const activeButton = document.querySelector('.status-button.selected');

            if (activeButton && activeButton.getAttribute('data-status') === status) {
                // If clicked filter is already active, remove the filter and show all tasks
                tasks.forEach(task => {
                    task.style.display = 'block';  // Show all tasks
                });
                buttons.forEach(button => {
                    button.classList.remove('selected');  // Remove selection from all filter buttons
                });
            } else {
                // Otherwise, apply the selected filter
                tasks.forEach(task => {
                    if (status === 'all' || task.getAttribute('data-status') === status) {
                        task.style.display = 'block';
                    } else {
                        task.style.display = 'none';
                    }
                });
                buttons.forEach(button => {
                    if (button.getAttribute('data-status') === status) {
                        button.classList.add('selected');  // Highlight the selected button
                    } else {
                        button.classList.remove('selected');  // Remove highlight from others
                    }
                });
            }
        }

        // Function to update task status when a button is clicked
        function updateTaskStatus(taskId, status) {
            fetch(`/tasks/${taskId}/update-status`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({ status: status })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update the status text on the page
                        const statusElement = document.querySelector(`.task-status[data-id="${taskId}"]`);
                        statusElement.textContent = status;

                        // Optional: Highlight the selected button
                        const buttons = document.querySelectorAll(`.task-item[data-id="${taskId}"] .status-btn`);
                        buttons.forEach(button => {
                            if (button.getAttribute('data-status') === status) {
                                button.classList.add('selected');
                            } else {
                                button.classList.remove('selected');
                            }
                        });
                    } else {
                        alert('Failed to update task status');
                    }
                })
                .catch(error => {
                    console.error('Error updating task status:', error);
                    alert('There was an error updating the task status');
                });
        }

        // Function to highlight the button based on the current status
        document.addEventListener('DOMContentLoaded', function() {
            const taskItems = document.querySelectorAll('.task-item');

            taskItems.forEach(task => {
                const taskStatus = task.getAttribute('data-status');
                const buttons = task.querySelectorAll('.status-btn');

                buttons.forEach(button => {
                    if (button.getAttribute('data-status') === taskStatus) {
                        button.classList.add('selected');  // Highlight the button
                    } else {
                        button.classList.remove('selected');  // Remove highlighting
                    }
                });
            });

            filterTasks('all');  // Show all tasks by default
        });
    </script>
@endpush

<style>
    /* Style for the status filter buttons */
    .status-button {
        padding: 10px 20px;
        margin: 5px;
        cursor: pointer;
        border: 1px solid #027478;
        background-color: #fff;
        color: #027478;
        border-radius: 5px;
    }

    .status-button.selected {
        background-color: #027478;
        color: #fff;
    }

    /* Style for task status change buttons */
    .status-btn {
        padding: 5px 10px;
        margin: 5px;
        cursor: pointer;
        border: 1px solid #027478;
        background-color: #fff;
        color: #027478;
        border-radius: 5px;
    }

    .status-btn.selected {
        background-color: #027478;
        color: #fff;
    }

    .task-item {
        margin-bottom: 10px;
    }
</style>
