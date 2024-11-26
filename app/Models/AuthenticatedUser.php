<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AuthenticatedUser extends Authenticatable
{

    use HasApiTokens, HasFactory, Notifiable;

    // Don't add create and update timestamps in database.
    public $timestamps  = false;

    protected $table = 'authenticated_user';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'user_creation_date',
        'suspended_status',
        'pfp',
        'pronouns',
        'bio',
        'country'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'user_creation_date' => 'date',
        'suspended_status' => 'boolean',
    ];

    /**
     * Get the projects for the user.
     */
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_member', 'id', 'project_id')
                    ->using(ProjectMember::class)
                    ->withPivot('role');
    }

    /**
     * Get the comments for the user.
     */
    public function taskComments()
    {
        return $this->hasMany(TaskComment::class);
    }

    /**
     * Get the notifications for the user.
     */
    public function notifs()
    {
        return $this->belongsToMany(Notif::class, 'authenticated_user_notif', 'id', 'notif_id');
    }

    /**
     * Get the tasks for the user.
     */
    public function tasks()
    {
        return $this->belongsToMany(Task::class, 'user_task', 'id', 'task_id');
    }

    /**
     * Get the creation date for the user.
     */
    public function getUserCreationDate()
    {
        return $this->user_creation_date;
    }
    
    /**
     * Get the username for the user.
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Get the suspended status for the user.
     */
    public function getSuspendedStatus(): bool
    {
        return $this->suspended_status;
    }

    /**
     * Get the pronouns for the user.
     */
    public function getPronouns(): string
    {
        return $this->pronouns;
    }

    /**
     * Get the bio for the user.
     */
    public function getBio(): string
    {
        return $this->bio;
    }

    /**
     * Get the country for the user.
     */
    public function getCountry(): string
    {
        return $this->country;
    }
}