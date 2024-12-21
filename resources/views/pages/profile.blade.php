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

        <button id="edit-profile-button">
            <i class="fa fa-pencil"></i> Edit Profile
        </button>

        @if($pfp && $pfp !== 'profile_pictures/default-profile.jpg')
            <form id="remove-image-form" method="POST" action="{{ route('profile.removeImage') }}" style="display: none;">
                @csrf
                @method('DELETE')
                <button type="submit" class="remove-button">Remove Profile Image</button>
            </form>
        @endif

        <form id="edit-profile-form" method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" style="display: none;">
            @csrf
            @method('PUT')

            <label for="pfp">Profile Picture</label>
            <input type="file" style="max-width: 95%;" name="pfp" id="pfp">

            <label for="pronouns">Pronouns</label>
            <input type="text" name="pronouns" id="pronouns" value="{{ $pronouns }}">

            <label for="bio">Bio (max 255 characters)</label>
            <textarea name="bio" style="max-width: 95%;" id="bio" maxlength="255">{{ $bio }}</textarea>

            <label for="country">Country</label>
            <input type="text" name="country" id="country" value="{{ $country }}">

            <button type="submit">Save Changes</button>
        </form>

        <form method="POST" action="{{ route('profile.delete') }}" style="margin-top: 20px;">
            @csrf
            @method('DELETE')
            <!-- Delete Account Button -->
            <button type="button" id="delete-account-btn" class="delete-button">
                Delete Account
            </button>

            <!-- Custom Popup Modal (Hidden by Default) -->
            <div id="delete-modal" style="display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 1000; justify-content: center; align-items: center;">
                <div style="background: white; padding: 20px; border-radius: 8px; text-align: center; width: 300px;">
                    <p style="margin-bottom: 20px;">Are you sure you want to delete your account? This action cannot be undone.</p>
                    <form id="delete-account-form" method="POST" action="{{ route('profile.delete') }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" style="background-color: red; color: white; border: none; padding: 10px 20px; margin-right: 10px; cursor: pointer; border-radius: 5px;">Yes, Delete</button>
                        <button type="button" id="cancel-delete-btn" style="background-color: grey; color: white; border: none; padding: 10px 20px; cursor: pointer; border-radius: 5px;">Cancel</button>
                    </form>
                </div>
            </div>

            <script>
                document.getElementById('delete-account-btn').addEventListener('click', function () {
                    document.getElementById('delete-modal').style.display = 'flex'; // Show the popup
                });

                document.getElementById('cancel-delete-btn').addEventListener('click', function () {
                    document.getElementById('delete-modal').style.display = 'none'; // Hide the popup
                });
            </script>

        </form>
    </div>

    <script>
        document.getElementById('edit-profile-button').addEventListener('click', function () {
            document.getElementById('edit-profile-form').style.display = 'block';
            @if($pfp && $pfp !== 'profile_pictures/default-profile.jpg')
            document.getElementById('remove-image-form').style.display = 'block';
            @endif
                this.style.display = 'none';
        });
    </script>
@endsection
