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

        @if (Auth::check() && Auth::user()->username === $username)
            <button id="edit-profile-button">
                <i class="fa fa-pencil"></i> Edit Profile
            </button>
        @else
        @if (Auth::check() && Auth::user()->isFollowing($user->id))
                <form method="POST" action="{{ route('profile.unfollow', $user->username) }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-danger">Unfollow</button>
                </form>
            @else
                <form method="POST" action="{{ route('profile.follow', $user->username) }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-primary">Follow</button>
                </form>
            @endif
        @endif

        <div style="margin-top: 20px;">
            <a href="{{ route('profile.followers', $username) }}">Followers</a> |
            <a href="{{ route('profile.following', $username) }}">Following</a>
        </div>

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

        @if (Auth::check() && Auth::user()->username === $username)
            <form method="POST" action="{{ route('profile.delete') }}" style="margin-top: 20px;">
                @csrf
                @method('DELETE')
                <button type="button" id="delete-account-btn" class="delete-button">
                    Delete Account
                </button>

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
                        document.getElementById('delete-modal').style.display = 'flex';
                    });

                    document.getElementById('cancel-delete-btn').addEventListener('click', function () {
                        document.getElementById('delete-modal').style.display = 'none';
                    });
                </script>
            </form>
        @endif
    </div>
    
    @if (Auth::check() && Auth::user()->username === $username)
        <script>
            document.getElementById('edit-profile-button').addEventListener('click', function () {
                document.getElementById('edit-profile-form').style.display = 'block';
                @if($pfp && $pfp !== 'profile_pictures/default-profile.jpg')
                document.getElementById('remove-image-form').style.display = 'block';
                @endif
                this.style.display = 'none';
            });
        </script>
    @endif
@endsection
