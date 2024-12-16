@extends('layouts.app')

@section('content')
    @include('admin.admin_header')

    <h1>Create User Account</h1>

    @if (session('success'))
        <div style="color: green; margin-bottom: 10px;">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.storeUser') }}" style="max-width: 500px; margin: 0 auto;">
        @csrf

        <label for="username">Username</label>
        <input id="username" type="text" name="username" value="{{ old('username') }}" required autofocus>
        @error('username')
        <span style="color: red;">{{ $message }}</span>
        @enderror

        <label for="email">E-Mail Address</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required>
        @error('email')
        <span style="color: red;">{{ $message }}</span>
        @enderror

        <label for="password">Password</label>
        <input id="password" type="password" name="password" required>
        @error('password')
        <span style="color: red;">{{ $message }}</span>
        @enderror

        <label for="password-confirm">Confirm Password</label>
        <input id="password-confirm" type="password" name="password_confirmation" required>

        <button type="submit" style="margin-top: 15px; background-color: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
            Register User
        </button>


    </form>
@endsection
