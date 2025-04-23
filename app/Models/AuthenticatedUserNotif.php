<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuthenticatedUserNotif extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // Don't add create and update timestamps in database.
    public $timestamps  = false;

    protected $table = 'authenticated_user_notif';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'notif_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id' => 'integer',
        'notif_id' => 'integer',
    ];

    /**
     * Get the notification associated with the user notification.
     */
    public function notif(): BelongsTo
    {
        return $this->belongsTo(Notif::class, 'notif_id');
    }

    /**
     * Get the authenticated user associated with the user notification.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(AuthenticatedUser::class, 'id');
    }

    /**
     * Get the title of the notification.
     */
    public function getTitle(): string
    {
        return $this->notif()->first()->title;
    }

    /**
     * Get the content of the notification.
     */
    public function getContent(): string
    {
        return $this->notif()->first()->content;
    }

    /**
     * Get the created_at timestamp of the notification.
     */
    public function getCreatedAt(): string
    {
        return $this->notif()->first()->created_at;
    }
}