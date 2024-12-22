@extends('layouts.app')

@section('content')
<div class="profile-container">
    <h1>{{ $task->task_name }}</h1>
    <p>Status: {{ $task->status }}</p>
    <p>Due date: {{ $task->due_date }}</p>
    <p>Details: {{ $task->details }}</p>

    <div id="comments-section">
        <h3>Comments</h3>
        <ul id="comments-list">
            @foreach ($task->comments as $comment)
                <li>
                    <strong>{{ $comment->user->username }}:</strong> {{ $comment->comment }}
                    <br>
                    <small>{{ $comment->created_at }}</small>
                </li>
            @endforeach
        </ul>

        @if (Auth::check())
            <form id="comment-form" action="{{ route('taskComments.store', $task) }}" method="POST">
                @csrf
                <textarea name="comment" rows="3" required maxlength="500"></textarea>
                <button type="submit">Add Comment</button>
            </form>
        @endif
    </div>
</div>

<script>
document.getElementById('comment-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = this;
    const formData = new FormData(form);
    
    fetch(form.action, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Add new comment to list
            const commentsList = document.getElementById('comments-list');
            const newComment = document.createElement('li');
            newComment.innerHTML = `
                <strong>${data.username}:</strong> ${data.comment}
                <br>
                <small>${data.created_at}</small>
            `;
            commentsList.appendChild(newComment);
            
            // Clear form
            form.reset();
        }
    })
    .catch(error => console.error('Error:', error));
});
</script>
@endsection