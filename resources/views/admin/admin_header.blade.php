<div>
    <a href="{{ route('admin.unsuspended_users') }}">Unsuspended Users</a>
    <a href="{{ route('admin.suspended_users') }}">Suspended Users</a>
    <a href="{{ route('admin.pleas_dashboard') }}">Pleas Dashboard</a>
    <a href="{{ route('admin.create_user') }}">Create User</a>

    <form action="{{ route('admin.logout') }}" method="POST" style="    display: inline;">
        @csrf
        <button type="submit">Logout</button>
    </form>
</div>
