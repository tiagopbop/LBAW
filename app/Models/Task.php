<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Task extends Model
{
    use HasFactory;

    // Don't add create and update timestamps in database.
    public $timestamps  = false;
    protected $table = 'task';
    protected $primaryKey = 'task_id';

    public $incrementing = true;

    protected $keyType = 'int';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'project_id',
        'task_name',
        'status',
        'details',
        'due_date',
        'priority',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'due_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the project that owns the task.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'project_id');
    }

    /**
     * Get the comments for the task.
     */
        public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class, 'task_id', 'task_id');
    }

    /**
     * Get the users assigned to the task.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(AuthenticatedUser::class, 'user_task', 'task_id', 'id');
    }

    public function getDueDateAttribute($value)
    {
        return $value ? $value : 'No Due Date';
    }

    public function getAssignedUsers($id)
    {
        $task = Task::findOrFail($id);  // Ensure the task exists
        $users = $task->users->pluck('username')->toArray();  // Get the usernames
        return response()->json($users);  // Return as JSON
    }


    public function assignedUsers(): BelongsToMany
    {
        return $this->belongsToMany(AuthenticatedUser::class, 'user_task', 'task_id', 'id');
    }

    public function searchTasks(Request $request)
    {
        $tasks = Task::where('project_id', $request->project_id)
            ->where('task_name', 'like', '%' . $request->query . '%')
            ->get();

        $tasksData = $tasks->map(function ($task) {
            return [
                'task_id' => $task->task_id,
                'task_name' => $task->task_name,
                'status' => $task->status,
                'details'=>$task->details,
                'due_date' => $task->due_date,
                'assigned_users' => $task->assignedUsers()->pluck('username')->toArray(),
                ];
        });

        return response()->json($tasksData);
    }

}