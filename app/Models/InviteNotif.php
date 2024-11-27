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

    protected $table = 'invite_notif';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'notif_id',
        'project_id',
    ];

    /**
     * Get the notification associated with the invite
     */
    public function notif(): BelongsTo
    {
        return $this->belongsTo(Notif::class, 'notif_id');
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

    public function getTitle()
    {
        return $this->notif()->getTitle();
    }

    /**
     * Get the content of the notification.
     */
    public function getContent()
    {
        return $this->notif()->getContent();
    }

    /**
     * Get the created_at of the notification.
     */
    public function getCreatedAt()
    {
        return $this->notif()->getCreatedAt();
    }
}