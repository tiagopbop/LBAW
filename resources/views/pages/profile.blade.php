@extends('layouts.app')

@section('content')
    <div class="profile-container">
        <h1>{{ $username }}</h1>
        <p>Email: {{ $email }}</p>

        <img src="{{ $pfp ? asset('storage/' . $pfp) : asset('storage/profile_pictures/default-profile.jpg') }}"
             alt="Profile Picture"
             style="max-width: 150px; border-radius: 50%;">



        <p>Pronouns: {{ $pronouns ?? 'Not specified' }}</p>
        <p>Bio: {{ $bio ?? 'No bio available' }}</p>
        <p>Country: {{ $country ?? 'Not specified' }}</p>

        <!-- Edit Button -->
        <button id="edit-profile-button">
            <i class="fa fa-pencil"></i> Edit Profile
        </button>

        <!-- Edit Form (hidden by default) -->
        <!-- Add this after the profile picture display -->
        <!-- Remove Profile Image Button (only visible when editing) -->
        @if($pfp && $pfp !== 'profile_pictures/default-profile.jpg')
            <form id="remove-image-form" method="POST" action="{{ route('profile.removeImage') }}" style="display: none;">
                @csrf
                @method('DELETE')
                <button type="submit" class="remove-button">Remove Profile Image</button>
            </form>
        @endif

        <!-- Edit Form (hidden by default) -->
        <form id="edit-profile-form" method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" style="display: none;">
            @csrf
            @method('PUT')

            <label for="pfp">Profile Picture</label>
            <input type="file" name="pfp" id="pfp">

            <label for="pronouns">Pronouns</label>
            <input type="text" name="pronouns" id="pronouns" value="{{ $pronouns }}">

            <label for="bio">Bio (max 255 characters)</label>
            <textarea name="bio" id="bio" maxlength="255">{{ $bio }}</textarea>

            <label for="country">Country</label>
            <input type="text" name="country" id="country" value="{{ $country }}">

            <button type="submit">Save Changes</button>
        </form>

    </div>
    <script>
        // Show edit form and remove button when "Edit Profile" is clicked
        document.getElementById('edit-profile-button').addEventListener('click', function () {
            document.getElementById('edit-profile-form').style.display = 'block';
            @if($pfp && $pfp !== 'profile_pictures/default-profile.jpg')
            document.getElementById('remove-image-form').style.display = 'block';  // Show the remove image button if a profile image exists
            @endif
                this.style.display = 'none';  // Hide the edit button
        });
    </script>
@endsection
