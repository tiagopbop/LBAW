<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskComment extends Model
{
    use HasFactory;

    public $timestamps  = false;
    protected $table = 'task_comments';
    protected $primaryKey = 'comment_id';
    public $incrementing = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id', 
        'task_id',
        'comment',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(AuthenticatedUser::class, 'id')->withTrashed();
    }

    public function getAuthorAttribute()
    {
        return $this->user ? ($this->user->trashed() ? '[Deleted]' : $this->user->username) : '[Deleted]';
    }
}