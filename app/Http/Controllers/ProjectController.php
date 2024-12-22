<?php

namespace App\Http\Controllers;

use App\Models\AuthenticatedUserNotif;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AuthenticatedUser;
use App\Models\Favorited;
use App\Models\Notif;
use App\Models\InviteNotif;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::all(); // Or apply any necessary filters
        return view('projects.index', compact('projects'));
    }

    public function create()
    {
        if (Auth::user()->suspended_status) {
            return redirect()->route('pleading.page')->with('error', 'Your account is suspended. Contact admin for further assistance.');
        }
        return view('projects.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_title' => 'required|string|max:50',
            'project_description' => 'nullable|string|max:500',
            'availability' => 'required|boolean',
        ]);

        $project = Project::create([
            'project_title' => $validated['project_title'],
            'project_description' => $validated['project_description'] ?? 'No description',
            'availability' => $validated['availability'],
            'archived_status' => false,
        ]);

        $project->members()->attach(Auth::id(), ['role' => 'Project owner']);

        Favorited::create([
            'id' => auth()->id(),
            'project_id' => $project->project_id,
            'checks' => false,
        ]);

        return redirect()->route('projects.myProjects');
    }

    public function show(Project $project) {

        if (Auth::check() && Auth::user()->suspended_status) {
            return redirect()->route('pleading.page')
                ->with('error', 'Your account is suspended. Contact admin for further assistance.');
        }

        $project->load(['tasks', 'members']);

        $sortedMembers = $project->members->sortBy(function ($member) {
            $roleOrder = ['Project owner' => 1, 'Project manager' => 2, 'Project member' => 3];
            return [$roleOrder[$member->pivot->role] ?? 4, $member->username];
        });
    
        return view('projects.show', compact('project', 'sortedMembers'));
    }

    public function myProjects()
    {
        if (Auth::user()->suspended_status) {
            return redirect()->route('pleading.page')->with('error', 'Your account is suspended. Contact admin for further assistance.');
        }
        $projects = auth()->user()->projects;

        return view('projects.myProjects', compact('projects'));
    }

    public function destroy(Project $project)
    {
        if (auth()->id() !== $project->members()->wherePivot('role', 'Project owner')->first()->id) {
            abort(403, 'Unauthorized action.');
        }

        $project->delete();

        return redirect()->route('projects.myProjects')->with('success', 'Project deleted successfully!');
    }

    public function invite(Request $request, Project $project)
    {
        $request->validate([
            'username' => 'required|string|exists:authenticated_user,username',
        ]);

        $user = AuthenticatedUser::where('username', $request->input('username'))->first();

        if (!$user) {
            return back()->withErrors(['username' => 'User not found.']);
        }

        // Check if the user is already a member of the project
        if ($project->members()->where('authenticated_user.id', $user->id)->exists()) {
            return back()->withErrors(['username' => 'User is already a member of this project.']);
        }

        $project->members()->attach($user->id, ['role' => 'Project member']);

        // Check if the user already has a notification for this project
        $existingInvite = InviteNotif::whereHas('notif.authenticatedUserNotifs', function ($query) use ($user) {
            $query->where('id', $user->id);
        })->where('project_id', $project->project_id)
            ->exists();

        if ($existingInvite) {
            return back()->with('error', 'The user has already been invited to this project.');
        }

        // Create a notification for the user
        $notification = Notif::create([
            'title' => 'Project Added',
            'content' => "You have been added to the project: {$project->project_title}.",
        ]);

        // Create the invite notification record
        $inviteNotif = InviteNotif::create([
            'notif_id' => $notification->notif_id,
            'project_id' => $project->project_id,
            'accepted' => false,
        ]);

        // Link the notification to the user
        AuthenticatedUserNotif::create([
            'id' => $user->id,
            'notif_id' => $notification->notif_id,
        ]);

        // Return success message
        return back()->with('success', 'User has been added successfully!');
    }


    public function assignManager(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'member_id' => 'required|exists:authenticated_user,id',
        ]);

        $member = $project->members()->where('authenticated_user.id', $validated['member_id'])->first();

        if (!$member) {
            return back()->withErrors(['member_id' => 'Member not found in this project.']);
        }

        $project->members()->updateExistingPivot($validated['member_id'], ['role' => 'Project manager']);

        return back()->with('success', 'Project manager assigned successfully!');
    }

    public function revertManager(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'member_id' => 'required|exists:authenticated_user,id',
        ]);

        $member = $project->members()->where('authenticated_user.id', $validated['member_id'])->first();

        if (!$member) {
            return back()->withErrors(['member_id' => 'Member not found in this project.']);
        }

        $project->members()->updateExistingPivot($validated['member_id'], ['role' => 'Project member']);

        return back()->with('success', 'Project manager reverted to member successfully!');
    }
    
    public function removeMember(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'member_id' => 'required|exists:authenticated_user,id',
        ]);

        // Check if the member to be removed is the project owner
        $owner = $project->members()->wherePivot('role', 'Project owner')->first();
        if ($owner && $owner->id == $validated['member_id']) {
            return back()->withErrors(['member_id' => 'The project owner cannot remove themselves from the project.']);
        }

        $project->members()->detach($validated['member_id']);

        return back()->with('success', 'Member removed from the project successfully!');
    }

    public function edit(Project $project)
    {
        $this->authorize('update', $project);

        return view('projects.edit', compact('project'));
    }

    public function update(Request $request, Project $project)
    {

        $validatedData = $request->validate([
            'project_title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'availability' => 'required|boolean',
            'archived_status' => 'required|boolean',
        ]);

        $project->update([
            'project_title' => $validatedData['project_title'],
            'project_description' => $validatedData['description'],
            'availability' => $validatedData['availability'],
            'archived_status' => $validatedData['archived_status'],
        ]);

        return redirect()->route('projects.show', $project)->with('success', 'Project updated successfully!');
    }

    public function leaveProject(Project $project)
    {
        $userId = auth()->id();

        $isMember = $project->members()->where('project_member.id', $userId)->exists();

        if (!$isMember) {
            return redirect()->route('projects.myProjects')->with('error', 'You are not a member of this project.');
        }

        $project->members()->detach($userId);

        return redirect()->route('projects.myProjects')->with('success', 'You have left the project.');
    }


    public function forum(Project $project)
{
    
    $posts = $project->posts()
        ->with(['replies' => function ($query) {
            $query->orderBy('reply_creation', 'desc'); 
        }])
        ->orderBy('post_creation', 'desc') 
        ->get();

    return view('projects.forum', compact('project', 'posts'));
}


}
