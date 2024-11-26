<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Notif extends Model
{
    use HasFactory;

    // Don't add create and update timestamps in database.
    public $timestamps  = false;

    protected $table = 'notif';

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
     * Get the users for the notification.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(AuthenticatedUser::class, 'authenticated_user_notif', 'notif_id', 'id');
    }

    /**
     * Get the title of the notification.
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Get the content of the notification.
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Get the creation date of the notification.
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }
}