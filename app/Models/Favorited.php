<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Favorited extends Model
{
    use HasFactory;

    // Don't add create and update timestamps in database.
    public $timestamps  = false;

    protected $table = 'favorited';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'project_id',
        'checks',
    ];

    /**
     * Get the user that the favorite is associated with
     */
    public function user() : BelongsTo
    {
        return $this->belongsTo(AuthenticatedUser::class, 'id');
    }

    /**
     * Get the project the favorite is associated with
     */
    public function project() : BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}