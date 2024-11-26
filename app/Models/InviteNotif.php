<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
     * Get the project associated with the invite
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
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