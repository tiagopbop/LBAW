@extends('layouts.app')

@section('title', 'My Projects')

@section('content')
<div>
    <h1>My Projects</h1>

    @forelse ($projects as $project)
        <div class="strip" style="border: 1px solid #ddd; border-radius: 10px; margin-bottom: 20px;">
            <div>
                <h3 style="margin: 0;">
                    {{ $project->project_title }}
                </h3>
                <p class="project-creation_date">
                    Created on: {{ $project->project_creation_date->format('Y-m-d') }}
                </p>
            </div>

            <div style="text-align: right;">
                <a href="{{ route('projects.show', $project) }}" class="view-project-button">
                    View Project
                </a>
                <form action="{{ route('projects.destroy', $project) }}" method="POST" style="display: inline-block; box-shadow: none; outline: none; background-color: #f4f6f8;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="delete-button" onclick="return confirm('Are you sure you want to delete this project?')">Delete Project</button>
                </form>
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




