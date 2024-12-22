<?php
// app/Models/Follow.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Follow extends Model
{
    use HasFactory;

    protected $fillable = [
        'follower_id',
        'followed_id',
    ];

    public function follower()
    {
        return $this->belongsTo(AuthenticatedUser::class, 'follower_id');
    }

    public function followed()
    {
        return $this->belongsTo(AuthenticatedUser::class, 'followed_id');
    }
}