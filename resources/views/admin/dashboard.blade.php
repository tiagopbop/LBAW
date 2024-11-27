@extends('layouts.app')

@section('content')
    <h1>Dashboard</h1>
    <form action="{{ route('admin.logout') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-danger">Logout</button>
    </form>
    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Joined</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($users as $user)
        <tr>
            <td>{{ $user->id }}</td>
            <td>{{ $user->username }}</td>
            <td>{{ $user->email }}</td>
            <td>{{ $user->user_creation_date->format('d M, Y') }}</td>
        </tr>
        @endforeach
        </tbody>
    </table>
@endsection
