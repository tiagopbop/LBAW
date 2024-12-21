@extends('layouts.app')

@section('title', 'My Projects')

@section('content')
<div class="profile-container" style="max-width: 100%; color: #027478;">
    <h1 style="color: #3e56b0;">My Projects</h1>

    @forelse ($projects as $project)
        <div class="strip" style="border: 1px solid #ddd; border-radius: 10px; margin-bottom: 20px;">
            <div>
                <h3 style="margin: 0;">
                    {{ $project->project_title }}
                </h3>
                <p class="project-creation_date" style="text-align: left; margin: 0;">
                    Created on: {{ $project->project_creation_date->format('Y-m-d') }}
                </p>
            </div>

            <div style="text-align: right;">
                <a href="{{ route('projects.show', $project) }}" class="view-project-button">
                    View Project
                </a>

                @php
                    $userRole = $project->members()
                        ->where('project_member.id', auth()->id())
                        ->first();

                    $userRole = $userRole ? $userRole->pivot->role : null;
                @endphp
                @if($userRole === 'Project owner' || $userRole === 'Project manager')
                    <a href="{{ route('projects.edit', $project) }}" class="view-project-button" style="background-color: #bfc900; margin-left: 20px;">
                        Edit Project
                    </a>
                @endif
                @if($userRole === 'Project owner')
                    <form action="{{ route('projects.destroy', $project) }}" method="POST" style="display: inline-block; box-shadow: none; outline: none;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="delete-button" onclick="return confirm('Are you sure you want to delete this project?')">Delete Project</button>
                    </form>
                @endif
            </div>
        </div>
    @empty
        <div style="margin-top: 20px;">
            You are not part of any projects yet.
        </div>
    @endforelse

    <div style="text-align: center;" >
        <a href="{{ route('projects.create') }}" class="large-button">
            Create Project
        </a>
    </div>
</div>
@endsection




