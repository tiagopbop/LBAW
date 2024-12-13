<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MyTasksController extends Controller
{
    public function myTasks()
    {
        $tasks = \App\Models\Task::join('user_task', 'task.task_id', '=', 'user_task.task_id') // Join on 'task_id'
            ->where('user_task.id', auth()->id()) // Match 'id' from 'user_task' to the authenticated user's ID
            ->select('task.*') // Select all columns from the 'task' table
            ->get();

        return view('pages.mytasks', compact('tasks'));
    }
}