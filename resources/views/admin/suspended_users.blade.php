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
                <td style="display: flex; gap: 10px;">
                    <form action="{{ route('admin.toggleSuspend', $user->id) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-success">Unsuspend</button>
                    </form>

                    <form method="POST" action="{{ route('admin.delete_users', $user->id) }}" style="display: inline;">
                        @csrf
                        @method('DELETE')

                        <button type="button" id="delete-user-btn-{{ $user->id }}" class="btn btn-danger" style="cursor: pointer;">
                            <i class="fa fa-trash"></i> Delete
                        </button>
                    </form>

                    <div id="delete-user-modal-{{ $user->id }}" style="display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 1000; justify-content: center; align-items: center;">
                        <div style="background: white; padding: 20px; border-radius: 8px; text-align: center; width: 300px;">
                            <p style="margin-bottom: 20px;">Are you sure you want to delete the user <strong>{{ $user->username }}</strong>? This action cannot be undone.</p>
                            <form method="POST" action="{{ route('admin.delete_users', $user->id) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="background-color: red; color: white; border: none; padding: 10px 20px; margin-right: 10px; cursor: pointer; border-radius: 5px;">
                                    Yes, Delete
                                </button>
                                <button type="button" id="cancel-delete-btn-{{ $user->id }}" style="background-color: grey; color: white; border: none; padding: 10px 20px; cursor: pointer; border-radius: 5px;">
                                    Cancel
                                </button>
                            </form>
                        </div>
                    </div>

                    <script>
                        document.getElementById('delete-user-btn-{{ $user->id }}').addEventListener('click', function () {
                            document.getElementById('delete-user-modal-{{ $user->id }}').style.display = 'flex'; // Show the popup
                        });

                        document.getElementById('cancel-delete-btn-{{ $user->id }}').addEventListener('click', function () {
                            document.getElementById('delete-user-modal-{{ $user->id }}').style.display = 'none'; // Hide the popup
                        });
                    </script>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
