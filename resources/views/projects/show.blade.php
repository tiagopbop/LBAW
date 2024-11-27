@extends('layouts.app')

@section('title', $project->project_title)

@section('content')
<div class="strip profile-container" style="margin-top: 20px; max-width: 100%;">
    <h1 style="font-weight: bold; color: #027478;">{{ $project->project_title }}</h1>

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