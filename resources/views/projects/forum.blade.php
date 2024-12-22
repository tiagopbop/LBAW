@extends('layouts.app')

@section('title', $project->project_title . ' Forum')

@section('content')
<div class="forum-container card shadow-sm">
    <div class="card-header bg-primary text-white">
        <h2 class="mb-0">Forum: {{ $project->project_title }}</h2>
    </div>
    <div class="card-body">
        <div class="mb-4">
            <h4>Add a new post</h4>
            <form action="{{ route('projects.addPost', $project) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <textarea name="content" class="form-control forum-textarea" rows="3" placeholder="Write a new post..."></textarea>
                </div>
                <button type="submit" class="btn btn-primary forum-btn">Add Post</button>
            </form>
        </div>

        <div class="posts">
            @foreach ($posts as $post)
                <div class="card mb-3 forum-post">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="card-title mb-0">
                                <strong>{{ $post->author }}</strong> posted:
                            </h5>
                            <div class="forum-actions">
                                <small class="text-muted">
                                    {{ $post->getPostCreation()->format('Y-m-d H:i') }}
                                </small>
                                @if(Auth::id() === $post->id || (Auth::user() && Auth::user()->pivot && Auth::user()->pivot->role === 'Project manager'))
                                    <form action="{{ route('posts.destroy', $post) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-delete-forum forum-btn" 
                                                onclick="return confirm('Are you sure you want to delete this post? All replies will be deleted too.')">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                        <p class="card-text">{{ $post->getContent() }}</p>

                        <!-- Replies -->
                        <div class="replies ms-4">
                            @foreach ($post->replies as $reply)
                                <div class="card mb-2 forum-reply">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="card-subtitle">
                                                <strong>{{ $reply->author }}</strong> replied:
                                            </h6>
                                            <div class="forum-actions">
                                                <small class="text-muted">
                                                    {{ $reply->getCreatedAt()->format('Y-m-d H:i') }}
                                                </small>
                                                @if(Auth::id() === $reply->id || (Auth::user() && Auth::user()->pivot && Auth::user()->pivot->role === 'Project manager'))
                                                    <form action="{{ route('replies.destroy', $reply) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn-delete-forum forum-btn" 
                                                                onclick="return confirm('Are you sure you want to delete this reply?')">
                                                            <i class="fas fa-trash"></i> Delete
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                        <p class="card-text">{{ $reply->content }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Reply Form -->
                        <form action="{{ route('posts.addReply', ['post' => $post->post_id]) }}" method="POST" class="mt-3">
                            @csrf
                            <div class="mb-3">
                                <textarea name="content" class="form-control forum-textarea" rows="2" placeholder="Write a reply..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-sm btn-primary forum-btn">Reply</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection