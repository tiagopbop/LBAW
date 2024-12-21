@extends('layouts.app')

@section('content')
    @include('admin.admin_header')

    <div class="container">
        <h1>Suspended Projects</h1>

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
                            <button type="submit" class="btn btn-success">Unsuspend</button>
                        </form>

                    <form method="POST" action="{{ route('admin.delete_project', $project->project_id) }}" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="button" id="delete-project-btn-{{ $project->project_id }}" class="btn btn-danger" style="display: inline-block; box-shadow: none; outline: none;">
                            <i class="fa fa-trash"></i> Delete
                        </button>
                    </form>

                    <!-- Delete Confirmation Modal -->
                    <div id="delete-project-modal-{{ $project->project_id }}" style="display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 1000; justify-content: center; align-items: center;">
                        <div style="background: white; padding: 20px; border-radius: 8px; text-align: center; width: 300px;">
                            <p style="margin-bottom: 20px;">Are you sure you want to delete the project <strong>{{ $project->project_title }}</strong>? This action cannot be undone.</p>
                            <form method="POST" action="{{ route('admin.delete_project', $project->project_id) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="background-color: red; color: white; border: none; padding: 10px 20px; margin-right: 10px; cursor: pointer; border-radius: 5px;">
                                    Yes, Delete
                                </button>
                                <button type="button" id="cancel-delete-btn-{{ $project->project_id }}" style="background-color: grey; color: white; border: none; padding: 10px 20px; cursor: pointer; border-radius: 5px;">
                                    Cancel
                                </button>
                            </form>
                        </div>
                    </div>

                    <script>
                        // Show delete confirmation modal
                        document.getElementById('delete-project-btn-{{ $project->project_id }}').addEventListener('click', function () {
                            document.getElementById('delete-project-modal-{{ $project->project_id }}').style.display = 'flex';
                        });

                        // Hide delete confirmation modal
                        document.getElementById('cancel-delete-btn-{{ $project->project_id }}').addEventListener('click', function () {
                            document.getElementById('delete-project-modal-{{ $project->project_id }}').style.display = 'none';
                        });
                    </script>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
