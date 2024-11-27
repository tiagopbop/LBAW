@extends('layouts.app')

@section('title', $project->project_title)

@section('content')
<div class="strip profile-container" style="margin-top: 20px; max-width: 100%;">
    <h1 style="font-weight: bold; color: #027478;">{{ $project->project_title }}</h1>

    <div class="invite-container" style="margin-top: 20px; padding: 15px; border: 1px solid #ddd; border-radius: 5px;">
        <h3>Invite User to Project</h3>
        <form action="{{ route('projects.invite', $project) }}" method="POST">
            @csrf
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required 
                placeholder="Enter the username" 
                style="width: 100%; padding: 10px; margin: 10px 0; border-radius: 5px; border: 1px solid #ddd;">
            <button type="submit" class="large-button">
                Invite User
            </button>
        </form>
    </div>

    @if(session('success'))
        <div class="success-message" style="margin-top: 10px; color: #28a745; font-weight: bold;">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="error-messages" style="margin-top: 10px; color: #dc3545; font-weight: bold;">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="strip" style="text-align: left;">
        <p style="text-align: left;"><strong>Description:</strong> {{ $project->project_description }}</p>
        <p style="text-align: left;"><strong>Availability:</strong> {{ $project->availability ? 'Public' : 'Private' }}</p>
        <p style="text-align: left;"><strong>Archived:</strong> {{ $project->archived_status ? 'Yes' : 'No' }}</p>
    </div>

    <div style="text-align: center;">
        <div class="strip" style="text-align: left;">
            <h4 style="font-weight: bold;">Members</h4>
            <ul>
                @foreach ($project->members as $member)
                    <li style="border: none; padding: 10px 0;">
                        {{ $member->username ?? 'Unknown' }} - {{ ucfirst($member->pivot->role ?? 'Member') }}
                    </li>
                @endforeach
            </ul>
        </div>
        <a href="{{ route('tasks.viewTasks', $project) }}" class="large-button">
            View Tasks
        </a>
    </div>
</div>
@endsection