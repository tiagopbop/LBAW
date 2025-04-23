@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>You Are Suspended</h1>
        <p>Your account has been suspended. Please contact support or submit a plea for reactivation.</p>

        @if (session('success'))
            <div class="alert alert-success" style="color: green; margin-bottom: 20px;">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('pleading.submit') }}" method="POST">
            @csrf
            <textarea name="plea" rows="5" placeholder="Write your plea here..." required></textarea>
            <button type="submit">Submit Plea</button>
        </form>
    </div>
@endsection
