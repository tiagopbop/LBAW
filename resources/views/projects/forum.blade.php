@extends('layouts.app')

@section('title', $project->project_title . ' Forum')

@section('content')
<div class="strip profile-container" style="margin-top: 20px; max-width: 100%;">
    <h1 style="font-weight: bold; color: #027478;">Forum: {{ $project->project_title }}</h1>
    <h3>Add a new post</h3>
    <form action="{{ route('projects.addPost', $project) }}" method="POST">
        @csrf
        <textarea name="content" rows="3" placeholder="Write a new post..." style="width: 100%;"></textarea>
        <button type="submit" class="btn btn-primary" style="margin-top: 10px;">Add Post</button>
    </form>
    <div >
        @foreach ($posts as $post)
            <div class="strip">
                <p><strong>{{$post->author}} posted:<br></strong>{{ $post->getContent() }} </p>
                <p>at: {{ $post->getPostCreation()->format('Y-m-d H:i') }}</p>
                <ul>
                    @foreach ($post->replies as $reply)
                        <div style="margin-bottom: 10px;">
                            <p><strong>{{$reply->author}} replied:</strong><br> 
                            {{ $reply->content }}</p>
                            <p>at: {{ $reply->getCreatedAt()->format('Y-m-d H:i') }}</p>          
                        </div>
                    @endforeach
                </ul>
                <form action="{{ route('posts.addReply', ['post' => $post->post_id]) }}" method="POST" style="margin-top: 5px;">
                    @csrf
                    <textarea name="content" rows="2" placeholder="Write a reply..." style="width: 100%;"></textarea>
                    <button type="submit" class="btn btn-primary" style="margin-top: 10px;">Reply</button>
                </form>
            </div>
        @endforeach
    </div>

    
</div>
@endsection
