<?php

namespace App\Http\Controllers;

use App\Models\Reply;
use Illuminate\Http\Request;

class ReplyController extends Controller
{
    public function destroy(Reply $reply)
    {
        // Check if user is reply author or project manager
        if (auth()->id() !== $reply->id && 
            !$reply->post->project->members()->where('id', auth()->id())->where('role', 'Project manager')->exists()) {
            abort(403);
        }

        $reply->delete();

        return redirect()->back()->with('success', 'Reply deleted successfully');
    }
}