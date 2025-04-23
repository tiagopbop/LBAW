@extends('layouts.app')

@section('content')
    <form method="POST" action="{{ route('login') }}">
        {{ csrf_field() }}

        <label for="email">E-mail</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
        @if ($errors->has('email'))
            <span class="error">
          {{ $errors->first('email') }}
        </span>
        @endif

        <label for="password" >Password</label>
        <input id="password" type="password" name="password" required>
        @if ($errors->has('password'))
            <span class="error">
            {{ $errors->first('password') }}
        </span>
        @endif

        <label>
            <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}> Remember Me
        </label>

        <button type="submit">
            Login
        </button>
        <a class="button button-outline" href="{{ route('register.submit') }}">Register</a>

        <p><a href="{{ route('password.request') }}">Forgot Password?</a></p>

        @if (session('success'))
            <p class="success">
                {{ session('success') }}
            </p>
        @endif
    </form>
    <div style="margin-top: 20px; text-align: center;">
        <a href="{{ route('admin.login') }}" class="button button-primary">Go to Admin Login</a>
    </div>
@endsection
