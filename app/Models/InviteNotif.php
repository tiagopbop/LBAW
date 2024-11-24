<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InviteNotification extends Model
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
        'project_id',
    ];

    /**
     * Get the notification that owns the invite notification.
     */
    public function notif(): BelongsTo
    {
        return $this->belongsTo(Notif::class, 'notif_id');
    }

    /**
     * Get the project that owns the invite notification.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}