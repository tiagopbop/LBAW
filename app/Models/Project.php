<?php

namespace App\Models;
use App\Models\AuthenticatedUser;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;

class Project extends Model
{
    use HasFactory;

    // Don't add create and update timestamps in database.
    public $timestamps  = false;

    protected $table = 'project';
    protected $primaryKey = 'project_id';
    public $incrementing = true;
    protected $keyType = 'int';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'availability',
        'project_creation_date',
        'archived_status',
        'updated_at',
        'project_title',
        'project_description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'project_creation_date' => 'date',
        'updated_at' => 'datetime',
        'archived_status' => 'boolean',
        'availability' => 'boolean',
    ];

    /**
     * Get the tasks for the project.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'project_id', 'project_id');
    }

    /**
     * Get the members of the project.
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(AuthenticatedUser::class, 'project_member', 'project_id', 'id')
                    ->using(ProjectMember::class)
                    ->withPivot('role')
                    ->orderBy('username');
    }

    /**
     * Get the posts for the project.
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'project_id');
    }

    ///////////// BASIC GETTERS //////////////////

    /**
     * Get the title for the project.
     */
    public function getTitle(): string
    {
        return $this->getAttribute('project_title');
    }

    /**
     * Get the description for the project.
     */
    public function getDescription(): string
    {
        return $this->getAttribute('project_description');
    }

    /**
     * Get the creation date for the project.
     */
    public function getCreationDate(): string
    {
        return $this->getAttribute('project_creation_date');
    }

    /**
     * Get the availability for the project.
     */
    public function getAvailability(): bool
    {
        return $this->getAttribute('availability');
    }

    /**
     * Get the archived status for the project.
     */
    public function getArchivedStatus(): bool
    {
        return $this->getAttribute('archived_status');
    }

    /**
     * Get the updated at for the project.
     */
    public function getUpdatedAt(): string
    {
        return $this->getAttribute('updated_at');
    }

    /**
     * Search projects by a term in the title or description.
     */
    
    

    public function scopeSearchByTerm(Builder $query, string $term): Builder
    {
        return $query->whereRaw("ts_vector_title_description @@ to_tsquery('english', ?)", [$term]);
    }
    
    /**
     * Show only public projects
     */
    public function scopePublic($query)
    {
        return $query->where('availability', true);
    }
}