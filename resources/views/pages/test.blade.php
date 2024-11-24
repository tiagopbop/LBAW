@extends('layouts.app')

@section('title', 'test')

@section('content')
    <section id="test">
        <h3>Your Information</h3>
        <p><strong>Username:</strong> {{ $username }}</p>
        <p><strong>Email:</strong> {{ $email }}</p>

        <!-- Logout Button -->
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-danger">Logout</button>
        </form>
    </section>
@endsection
