<?php

namespace App\Models;

use App\Models\AuthenticatedUser;
use App\Models\AuthenticatedUserNotif;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;


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
     * Get the authenticated user associated with the task notification.
     */
    public function user(): HasManyThrough
    {
        return $this->hasManyThrough(
            AuthenticatedUser::class,
            AuthenticatedUserNotif::class,
            'notif_id', // Foreign key on AuthenticatedUserNotif table...
            'id', // Foreign key on AuthenticatedUser table...
            'notif_id', // Local key on TaskNotif table...
            'id' // Local key on AuthenticatedUserNotif table...
        );
    }

    /**
     * Get the title of the notification.
     */
    public function getTitle()
    {
        return $this->notif()->first()->title;
    }

    /**
     * Get the content of the notification.
     */
    public function getContent()
    {
        return $this->notif()->first()->content;
    }

    /**
     * Get the created_at of the notification.
     */
    public function getCreatedAt()
    {
        return $this->notif()->first()->created_at;
    }
}