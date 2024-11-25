@extends('layouts.app')

@section('content')
    <h1>Create a New Project</h1>
    <form action="{{ route('projects.store') }}" method="POST">
        @csrf
        <div>
            <label for="project_title">Project Title:</label>
            <input type="text" id="project_title" name="project_title" required maxlength="50">
        </div>
        <div>
            <label for="project_description">Description:</label>
            <textarea id="project_description" name="project_description" maxlength="500"></textarea>
        </div>
        <div>
            <label for="availability">Availability:</label>
            <select id="availability" name="availability" required>
                <option value="1">Available</option>
                <option value="0">Unavailable</option>
            </select>
        </div>
        <button type="submit">Create Project</button>
    </form>
@endsection
