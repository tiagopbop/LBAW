@extends('layouts.app')

@section('title', 'My Projects')

@section('content')
<div class="container">
    <h1 class="mb-4 text-center">My Projects</h1>

    @forelse ($projects as $project)
        <div class="card mb-4 shadow-sm" style="border: 1px solid #ddd; border-radius: 10px; margin-bottom: 20px;">
            <div class="card-body d-flex justify-content-between align-items-center" style="padding: 20px;">
                <div class="project-details d-flex align-items-center" style="flex: 2;">
                    <h3 class="card-title" style="font-weight: bold; font-size: 18px; color: #333; margin: 0; margin-right: 15px;">
                        {{ $project->project_title }}
                    </h3>
                    <p style="font-size: 14px; color: rgba(0, 0, 0, 0.6); margin: 0;">
                        Created on: {{ $project->project_creation_date->format('Y-m-d') }}
                    </p>
                </div>

                <div class="view-button" style="flex: 1; text-align: right;">
                    <a href="{{ route('projects.show', $project) }}" class="btn view-project-btn">
                        View Project
                    </a>
                    <form action="{{ route('projects.destroy', $project) }}" method="POST" style="display: inline-block; box-shadow: none; outline: none; background-color: #f4f6f8;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this project?')">Delete Project</button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="alert alert-info text-center" style="margin-top: 20px;">
            You are not part of any projects yet.
        </div>
    @endforelse

    <div style="margin-top: 30px;" >
        <a href="{{ route('projects.create') }}" 
           class="btn btn-success create-project-btn"
           style="padding: 12px 25px; font-size: 18px; border-radius: 5px; text-decoration: none;">
            Create Project
        </a>
    </div>
</div>
@endsection




