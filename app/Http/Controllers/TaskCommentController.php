<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Task;
use App\Models\TaskComment;
use Carbon\Carbon;

class TaskCommentController extends Controller
{
    public function store(Request $request, Task $task)
    {
        $request->validate([
            'comment' => 'required|string|max:500',
        ]);

        $comment = TaskComment::create([
            'task_id' => $task->task_id,
            'id' => Auth::id(),
            'comment' => $request->input('comment'),
            'created_at' => Carbon::now(),
        ]);

        return response()->json([
            'success' => true,
            'username' => Auth::user()->username,
            'comment' => $comment->comment,
            'created_at' => $comment->created_at->format('Y-m-d H:i:s'),
        ]);
    }
}