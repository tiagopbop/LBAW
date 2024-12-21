@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Admin Login</h2>

        <form method="POST" action="{{ route('admin.login') }}">
            @csrf

            <div class="form-group">
                <label for="admin_tag">Admin Tag</label>
                <input type="text" name="admin_tag" id="admin_tag" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary">Login</button>
        </form>

        @if ($errors->any())
            <div class="alert alert-danger mt-2">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div style="margin-top: 20px; text-align: center;">
            <a href="{{ route('login') }}" class="button button-primary">Go to Normal Login</a>
        </div>
    </div>
@endsection
