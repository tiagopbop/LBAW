@extends('layouts.app')

@section('content')
    @include('admin.admin_header')

    <h1>Suspended Users</h1>
    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($users as $user)
            <tr>
                <td>{{ $user->id }}</td>
                <td>{{ $user->username }}</td>
                <td>{{ $user->email }}</td>
                <td>
                    <form action="{{ route('admin.toggleSuspend', $user->id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit">Unsuspend</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
