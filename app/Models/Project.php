<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
                    ->withPivot('role');
    }

    /**
     * Get the posts for the project.
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
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
     * Set the title for the project.
     */
    public function setTitle(string $title): void
    {
        $this->setAttribute('project_title', $title);
    }

    /**
     * Set the description for the project.
     */
    public function setDescription(string $description): void
    {
        $this->setAttribute('project_description', $description);
    }

    /**
     * Set the availability for the project.
     */
    public function setAvailability(bool $availability): void
    {
        $this->setAttribute('availability', $availability);
    }

    /**
     * Set the archived status for the project.
     */
    public function setArchivedStatus(bool $archivedStatus): void
    {
        $this->setAttribute('archived_status', $archivedStatus);
    }

    /**
     * Delete the project.
     */
    public function deleteProject(): void
    {
        $this->delete();
    }

    /**
     * Archive the project.
     */
    public function archiveProject(): void
    {
        $this->update(['archived_status' => true]);
    }

    /**
     * Restore the project.
     */
    public function restoreProject(): void
    {
        $this->update(['archived_status' => false]);
    }
}