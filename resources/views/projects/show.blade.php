@extends('layouts.app')

@section('title', $project->project_title)

@section('content')
<div class="strip" style="margin-top: 20px;">
    <h1 style="font-weight: bold;">{{ $project->project_title }}</h1>

    <div class="strip">
        <p><strong>Description:</strong> {{ $project->project_description }}</p>
        <p><strong>Availability:</strong> {{ $project->availability ? 'Public' : 'Private' }}</p>
        <p><strong>Archived:</strong> {{ $project->archived_status ? 'Yes' : 'No' }}</p>
    </div>

    <div style="text-align: center;">
        <div>
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
        </div>
        <a href="{{ route('tasks.viewTasks', $project) }}" class="large-button">
            View Tasks
        </a>
    </div>
</div>
@endsection