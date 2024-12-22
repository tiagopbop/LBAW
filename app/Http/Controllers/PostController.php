<?php
namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Project;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function addPost(Request $request, Project $project)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $project->posts()->create([
            'content' => $validated['content'],
            'id' => auth()->id(),
            'post_creation' => now(), // This includes both date and time
        ]);

        return redirect()->route('projects.forum', $project)->with('success', 'Post added successfully!');
    }

    


    public function addReply(Request $request, Post $post)
    {
        $validated = $request->validate([
            'content' => 'required|string',
        ]);
    
        // Create the reply
        $post->replies()->create([
            'content' => $validated['content'],
            'id' => auth()->id(),
            'reply_creation' => now(), // This includes both date and time
        ]);
        
    
        // Redirect back to the project forum
        return redirect()->route('projects.forum', $post->project)->with('success', 'Reply added successfully!');
    }
}
