<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reply extends Model
{
    use HasFactory;

    // Don't add create and update timestamps in database.
    public $timestamps  = false;

    protected $table = 'reply';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'post_id',
        'id',
        'content',
        'reply_creation',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'reply_creation' => 'datetime',
    ];

    /**
     * Get the post that owns the reply.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Get the content of the reply.
     */
    public function getContent()
    {
        return $this->getAttribute('content');
    }

    /**
     * Get the created_at of the reply.
     */
    public function getCreatedAt()
    {
        return $this->getAttribute('reply_creation');
    }
}