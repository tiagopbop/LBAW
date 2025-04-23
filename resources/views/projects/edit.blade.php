@extends('layouts.app')

@section('content')
<div class="profile-container">
    
    <h1>Edit Project</h1>

    <form action="{{ route('projects.update', $project) }}" method="POST" id="edit-profile-form">
        @csrf
        @method('PUT')

        <label for="project_title">Project Title</label>
        <input type="text" name="project_title" id="project_title" value="{{ $project->project_title }}" required>

        <label for="description">Description</label>
        <textarea name="description" id="description" style="max-width: 95%;">{{ $project->project_description }}</textarea>

        <label for="availability">Availability</label>
        <select name="availability" id="availability" class="strip">
            <option value="1" {{ $project->availability ? 'selected' : '' }}>Public</option>
            <option value="0" {{ !$project->availability ? 'selected' : '' }}>Private</option>
        </select>

        <label for="archived_status">Archived</label>
        <select name="archived_status" id="archived_status" class="strip">
            <option value="1" {{ $project->archived_status ? 'selected' : '' }}>Yes</option>
            <option value="0" {{ !$project->archived_status ? 'selected' : '' }}>No</option>
        </select>

        <button type="submit">Save Changes</button>
    </form>

    @if ($errors->any())
        <div>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

</div>
@endsection
