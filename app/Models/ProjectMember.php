<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectMember extends Pivot
{
    protected $table = 'project_member';

    protected $fillable = [
        'project_id',
        'user_id',
        'role',
    ];

    /**
     * Get the project that the member belongs to.
     */
    public function project() : BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user class of the member
     */
    public function user() : BelongsTo
    {
        return $this->belongsTo(AuthenticatedUser::class);
    }
}