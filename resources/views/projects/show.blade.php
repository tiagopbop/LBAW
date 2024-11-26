@extends('layouts.app')

@section('title', $project->project_title)

@section('content')
<div class="container" style="margin-top: 20px;">
    <h1 class="mb-4 text-center" style="font-weight: bold;">{{ $project->project_title }}</h1>

    <div class="card shadow-sm mb-4" style="padding: 20px; border-radius: 10px; border: 1px solid #ddd;">
        <p><strong>Description:</strong> {{ $project->project_description }}</p>
        <p><strong>Availability:</strong> {{ $project->availability ? 'Available' : 'Unavailable' }}</p>
        <p><strong>Archived:</strong> {{ $project->archived_status ? 'Yes' : 'No' }}</p>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm mb-4" style="padding: 20px; border-radius: 10px; border: 1px solid #ddd;">
                <h4 class="mb-3" style="font-weight: bold;">Members</h4>
                <ul class="list-group">
                    @foreach ($project->members as $member)
                        <li class="list-group-item" style="border: none; padding: 10px 0;">
                            {{ $member->username ?? 'Unknown' }} - {{ ucfirst($member->pivot->role ?? 'Member') }}
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
        <a href="{{ route('tasks.viewTasks', $project) }}" class="btn btn-success create-project-btn"
        style="padding: 12px 25px; font-size: 18px; border-radius: 5px; text-decoration: none;">View Tasks</a>
    </div>
</div>
@endsection