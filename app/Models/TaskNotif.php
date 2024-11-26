<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskNotif extends Model
{
    use HasFactory;

    // Don't add create and update timestamps in database.
    public $timestamps  = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'notif_id',
        'task_id',
    ];

    /**
     * Get the notification that owns the task notification.
     */
    public function notif(): BelongsTo
    {
        return $this->belongsTo(Notif::class, 'notif_id');
    }

    /**
     * Get the task that owns the task notification.
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    /**
     * Get the title of the notification.
     */
    public function getTitle()
    {
        return $this->notif()->title;
    }

    /**
     * Get the content of the notification.
     */
    public function getContent()
    {
        return $this->notif()->content;
    }

    /**
     * Get the created_at of the notification.
     */
    public function getCreatedAt()
    {
        return $this->notif()->created_at;
    }
}