@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
    <div class="container">
        <h1 style="font-weight: bold; color: #027478;">Your Notifications</h1>

        @if ($notifications->isEmpty())
            <p>You have no notifications.</p>
        @else
            <div class="notifications-list" style="margin-top: 20px;">
                @foreach ($notifications as $notification)
                    <div class="notification-item" style="padding: 10px; border-bottom: 1px solid #ddd; margin-bottom: 10px;">
                        <strong>{{ $notification->title }}</strong>
                        <p>{{ $notification->content }}</p>
                        <p style="font-size: 12px; color: #888;">{{ $notification->created_at->diffForHumans() }}</p>


                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
