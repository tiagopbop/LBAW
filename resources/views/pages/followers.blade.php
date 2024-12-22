<!-- resources/views/pages/followers.blade.php -->

@extends('layouts.app')

@section('content')
    <div class="profile-container">
        <h1>{{ $user->username }}'s Followers</h1>
        <ul>
            @foreach ($followers as $follow)
                <li>
                    <a href="{{ route('profile.show', $follow->follower->username) }}">{{ $follow->follower->username }}</a>
                </li>
            @endforeach
        </ul>
    </div>
@endsection