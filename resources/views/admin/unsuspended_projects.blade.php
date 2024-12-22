@extends('layouts.app')

@section('content')
    @include('admin.admin_header')

    <div class="container">
        <h1>Active Projects</h1>

        @if(session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif

        <table class="table table-striped">
            <thead>
            <tr>
                <th>ID</th>
                <th>Project Name</th>
                <th>Description</th>
                <th>Availability</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($projects as $project)
                <tr>
                    <td>{{ $project->project_id }}</td>
                    <td>{{ $project->project_title }}</td>
                    <td>{{ $project->project_description }}</td>
                    <td>{{ $project->availability ? 'Public' : 'Private' }}</td>
                    <td>
                        <form action="{{ route('admin.toggleProjectSuspend', $project->project_id) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-warning">Suspend</button>
                        </form>

                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
