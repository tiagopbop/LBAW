@extends('layouts.app')

@section('title', $project->project_title)

@section('content')
<div class="strip profile-container" style="margin-top: 20px; max-width: 100%;">
    <h1 style="font-weight: bold; color: #027478;">{{ $project->project_title }}</h1>
    @php
        $member = $project->members()->where('project_member.id', auth()->id())->first();
        $userRole = $member ? $member->pivot->role : 'Guest'; // Default to 'Guest' or another fallback role
    @endphp
        @if(in_array($userRole, ['Project owner', 'Project manager']))
        <div style="margin-top: 20px; padding: 15px; border: 1px solid #ddd; border-radius: 5px;">
            <h3>Invite User to Project</h3>
                <form action="{{ route('projects.invite', $project) }}" method="POST">
                    @csrf
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                    <button type="submit" class="btn btn-primary">Invite</button>
                </form>
        </div>
    @endif

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

    <div class="strip" style="text-align: left; margin-top: 20px;">
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