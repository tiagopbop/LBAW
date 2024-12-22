<?php

namespace App\Models;

use App\Models\AuthenticatedUser;
use App\Models\AuthenticatedUserNotif;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class InviteNotif extends Model
{
    use HasFactory;

    // Don't add create and update timestamps in database.
    public $timestamps  = false;
    protected $primaryKey = 'invite_notif_id';
    protected $table = 'invite_notif';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['user_id', 'project_id', 'title', 'content', 'accepted'];



    /**
     * Get the notification associated with the invite
     */
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'project_id');
    }

    public function notif()
    {
        return $this->belongsTo(Notif::class, 'notif_id', 'notif_id');
    }

    /**
     * Get the authenticated user associated with the invite notification.
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

    public function getTitle() : string
    {
        return $this->notif()->first()->title;
    }

    /**
     * Get the content of the notification.
     */
    public function getContent() : string
    {
        return $this->notif()->first()->content;
    }

    /**
     * Get the created_at of the notification.
     */
    public function getCreatedAt() : string
    {
        return $this->notif()->first()->createdAt;
    }
}