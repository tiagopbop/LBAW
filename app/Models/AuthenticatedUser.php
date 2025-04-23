<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuthenticatedUser extends Authenticatable
{

    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    // Don't add create and update timestamps in database.
    public $timestamps  = false;
    protected $dates = ['deleted_at'];

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
     * Get the favorited projects for the user.
     */
    public function favorites()
    {
        return $this->hasMany(Favorited::class, 'id', 'id');
    }

    /**
     * Get the comments for the user.
     */
    public function taskComments() : hasMany
    {
        return $this->hasMany(TaskComment::class);
    }

    /**
     * Get the notifications for the user.
     */
    public function notifs() : belongsToMany
    {
        return $this->belongsToMany(Notif::class, 'authenticated_user_notif', 'id', 'notif_id');
    }

    /**
     * Get the tasks for the user.
     */
    public function tasks() : belongsToMany
    {
        return $this->belongsToMany(Task::class, 'user_task', 'id', 'task_id');
    }

    /**
     * Get the user's followers
     */
    public function followers()
    {
        return $this->hasMany(Follow::class, 'followed_id');
    }

    /**
     * Get the user's following
     */
    public function following()
    {
        return $this->hasMany(Follow::class, 'follower_id');
    }

    public function isFollowing($userId)
    {
        return $this->following()->where('followed_id', $userId)->exists();
    }

    /**
     * Get the creation date for the user.
     */
    public function getUserCreationDate()
    {
        return $this->getAttribute('user_creation_date');
    }
    
    /**
     * Get the username for the user.
     */
    public function getUsername()
    {
        return $this->getAttribute('username');
    }

    /**
     * Get the suspended status for the user.
     */
    public function getSuspendedStatus(): bool
    {
        return $this->getAttribute('suspended_status');
    }

    /**
     * Get the pronouns for the user.
     */
    public function getPronouns(): string
    {
        return $this->getAttribute('pronouns');
    }

    /**
     * Get the bio for the user.
     */
    public function getBio(): string
    {
        return $this->getAttribute('bio');
    }

    /**
     * Get the country for the user.
     */
    public function getCountry(): string
    {
        return $this->getAttribute('country');
    }
}