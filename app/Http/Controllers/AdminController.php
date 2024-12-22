<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;
use App\Models\AuthenticatedUser;
use App\Models\Plea;


class AdminController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.supersecretlogin');
    }

    public function login(Request $request)
    {
        $request->validate([
            'admin_tag' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $admin = Admin::where('admin_tag', $request->admin_tag)->first();

        if ($admin && Hash::check($request->password, $admin->password)) {
            session(['admin_id' => $admin->admin_id]);

            return redirect()->route('admin.unsuspended_users');
        }

        return back()->withErrors(['admin_tag' => 'Invalid credentials.']);
    }

    public function unsuspendedUsers()
    {
        $users = AuthenticatedUser::where('suspended_status', false)->get();
        return view('admin.unsuspended_users', compact('users'));
    }

    public function suspendedUsers()
    {
        $users = AuthenticatedUser::where('suspended_status', true)->get();
        return view('admin.suspended_users', compact('users'));
    }

    public function pleasDashboard()
    {
        $pleas = Plea::with('user')->get();
        return view('admin.pleas_dashboard', compact('pleas'));
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.loginForm');
    }

    public function toggleSuspend(Request $request, $id)
    {
        $user = AuthenticatedUser::findOrFail($id);

        $user->suspended_status = !$user->suspended_status;
        $user->save();

        return redirect()->route('admin.unsuspended_users')->with('success', 'User suspension status updated successfully.');
    }




    public function deleteUser($id)
    {
        $users = AuthenticatedUser::findOrFail($id);
        $users->delete();

        return redirect()->route('admin.suspended_users')->with('success', 'User deleted successfully.');
    }

    public function showCreateUserForm()
    {
        return view('admin.create_user');
    }

    public function create_user(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:authenticated_user',
            'email' => 'required|email|max:255|unique:authenticated_user',
            'password' => 'required|min:8|confirmed',
        ]);


        AuthenticatedUser::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_creation_date' => now(),
            'suspended_status' => false,
            'pfp' => null,
            'pronouns' => null,
            'bio' => null,
            'country' => null,
        ]);



        return redirect()->route('admin.create_user')->with('success', 'User account created successfully.');
    }



    public function toggleProjectSuspend($id)
    {
        $project = Project::findOrFail($id);

        if (($project->availability && $project->archived_status)||(!$project->archived_status)) {
            // If project is public and archived, change it to archived and private
            $project->availability = false; // Set private
            $project->archived_status = true; // Ensure it's archived
        } else {
            // If project is not public and not archived (or suspended), make it public and not archived
            $project->availability = true; // Set public
            $project->archived_status = false; // Set not archived
        }

        $project->save();

        return redirect()->route('admin.suspended_projects');
    }


    public function deleteProject($id)
    {
        $project = Project::findOrFail($id);
        $project->delete();

        return redirect()->route('admin.suspended_projects');
    }
    public function unsuspendedProjects()
    {

        $projects = Project::where(function ($query) {
            $query->where('archived_status', false) // Not archived
            ->orWhere(function ($query) {
                $query->where('archived_status', true) // Archived
                ->where('availability', true); // Public
            });
        })->get();

        return view('admin.unsuspended_projects', compact('projects'));
    }

    public function suspendedProjects()
    {

        $projects = Project::where('archived_status', true)
            ->where('availability', false)
          ->get();
        return view('admin.suspended_projects', compact('projects'));
    }


}