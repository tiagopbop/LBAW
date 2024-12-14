<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MyTasksController extends Controller
{
    public function myTasks()
    {
        $tasks = \App\Models\Task::join('user_task', 'task.task_id', '=', 'user_task.task_id') // Join on 'task_id'
            ->join('project', 'task.project_id', '=', 'project.project_id')
            ->where('user_task.id', auth()->id()) // Match 'id' from 'user_task' to the authenticated user's ID
            ->select('task.*', 'project.project_title') 
            ->orderByRaw("
            CASE 
                WHEN task.status = 'Ongoing' THEN 1
                WHEN task.status = 'On-hold' THEN 2
                WHEN task.status = 'Finished' THEN 3
                ELSE 4
            END
            ")
            ->orderBy('task.due_date', 'asc')
            ->get();

        return view('pages.mytasks', compact('tasks'));
    }
}