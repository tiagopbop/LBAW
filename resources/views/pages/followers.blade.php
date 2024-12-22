@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h2 class="mb-0">{{ $user->username }}'s Followers</h2>
        </div>
        
        <div class="card-body">
            <div class="nav-links mb-4">
                <a href="{{ route('profile.following', $user->username) }}" class="nav-link-custom">Following</a>
                <span class="nav-divider">|</span>
                <a href="{{ route('profile.show', $user->username) }}" class="nav-link-custom">Return to Profile</a>
            </div>

            <div class="user-list">
                @foreach ($followers as $follow)
                    <div class="user-item">
                        <a href="{{ route('profile.show', $follow->follower->username) }}" class="user-link">
                            {{ $follow->follower->username }}
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection