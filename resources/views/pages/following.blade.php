@extends('layouts.app')

@section('content')
    <div class="profile-container">
        <div style="margin-top: 20px;">
            <a href="{{ route('profile.followers', $user->username) }}">Followers</a> |
            <a href="{{ route('profile.show', $user->username) }}">Return to Profile</a>
        </div>
        <h1>{{ $user->username }}'s Following</h1>
        <ul>
            @foreach ($following as $follow)
                <li>
                    <a href="{{ route('profile.show', $follow->followed->username) }}">{{ $follow->followed->username }}</a>
                </li>
            @endforeach
        </ul>
    </div>
@endsection