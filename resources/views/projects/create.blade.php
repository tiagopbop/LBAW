@extends('layouts.app')

@section('content')
<div class="profile-container">
    <h1>
        Create a New Project
    </h1>
    <form id="edit-profile-form" action="{{ route('projects.store') }}" method="POST">
        @csrf
        <div>
            <label for="project_title">Project Title:</label>
            <input type="text" id="project_title" name="project_title" required maxlength="50">
        </div>
        <div>
            <label for="project_description">Description:</label>
            <textarea id="project_description" name="project_description" style="resize: none; width: 100%; height: 2cm; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; margin-bottom: 15px; box-sizing: border-box;" maxlength="500"></textarea>
        </div>
        <div>
            <label for="availability">Availability:</label>
            <select id="availability" name="availability" required style="resize: none; width: 25%; height: 1cm; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; margin-bottom: 15px; box-sizing: border-box;">
                <option value="1">Public</option>
                <option value="0">Private</option>
            </select>
        </div>
        <button type="submit">Create Project</button>
    </form>
</div>
@endsection
