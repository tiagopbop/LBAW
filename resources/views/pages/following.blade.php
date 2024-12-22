@extends('layouts.app')

@section('content')
    <div class="profile-container">
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