<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    use HasFactory;

    // Don't add create and update timestamps in the database.
    public $timestamps  = false;

    // Define the primary key as 'post_id'.
    protected $primaryKey = 'post_id';

    // The primary key is auto-incrementing.
    public $incrementing = true;

    // Define the data type of the primary key.
    protected $keyType = 'int';

    // Set the table name explicitly.
    protected $table = 'post';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'project_id', // Foreign key to the 'project' table.
        'id',         // Foreign key to the 'authenticated_user' table.
        'content',    // Post content.
        'post_creation', // Post creation date.
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'post_creation' => 'datetime', // Cast 'post_creation' as a datetime.
    ];

    /**
     * Define a relationship to the Project model.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    /**
     * Define a relationship to the AuthenticatedUser model.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(AuthenticatedUser::class, 'id'); // Link 'id' to 'authenticated_user' table.
    }

    /**
     * Define a relationship to the Reply model.
     */
    public function replies(): HasMany
    {
        return $this->hasMany(Reply::class, 'post_id');
    }

    /**
     * Accessor for the post content.
     */
    public function getContent()
    {
        return $this->getAttribute('content');
    }

    /**
     * Accessor for the post creation date.
     */
    public function getPostCreation()
    {
        return $this->getAttribute('post_creation');
    }

    public function getID()
    {
        return $this->user ? $this->user->username : null;
    }
}
