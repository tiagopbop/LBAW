@extends('layouts.app')

@section('content')
    @include('admin.admin_header')

    <h1>Pleas Dashboard</h1>
    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>User</th>
            <th>Plea</th>
            <th>Submitted At</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($pleas as $plea)
            <tr>
                <td>{{ $plea->id }}</td>
                <td>{{ $plea->authenticated_user_id }}</td>
                <td>{{ $plea->plea }}</td>
                <td>{{ $plea->created_at->format('d M, Y H:i') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
