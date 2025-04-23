<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Notif extends Model
{
    use HasFactory;

    // Don't add create and update timestamps in database.
    public $timestamps = false;

    protected $table = 'notif';
    protected $primaryKey = 'notif_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'content',
        'created_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Get the authenticated user associated with the notification.
     */
    public function user(): HasOneThrough
    {
        return $this->hasOneThrough(
            AuthenticatedUser::class,
            AuthenticatedUserNotif::class,
            'notif_id', // Foreign key on AuthenticatedUserNotif table...
            'id', // Foreign key on AuthenticatedUser table...
            'notif_id', // Local key on InviteNotif table...
            secondLocalKey: 'id' // Local key on AuthenticatedUserNotif table...
        );
    }

    /**
     * Get the authenticated user notifications associated with the notification.
     */
    public function authenticatedUserNotifs(): HasMany
    {
        return $this->hasMany(AuthenticatedUserNotif::class, 'notif_id');
    }

    /**
     * Get the title of the notification.
     */
    public function getTitle()
    {
        return $this->getAttribute('title');
    }

    /**
     * Get the content of the notification.
     */
    public function getContent()
    {
        return $this->getAttribute('content');
    }

    /**
     * Get the creation date of the notification.
     */
    public function getCreatedAt()
    {
        return $this->getAttribute('created_at');
    }

    public function inviteNotifs()
    {
        return $this->hasMany(InviteNotif::class, 'notif_id', 'notif_id');
    }

}