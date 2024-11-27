<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plea extends Model
{
    use HasFactory;
    protected $table = 'pleas';
    public $timestamps = false;

    protected $fillable = [
        'authenticated_user_id',
        'plea',
    ];


    protected $casts = [
        'created_at' => 'datetime',
    ];


    public function user()
    {
        return $this->belongsTo(AuthenticatedUser::class,'authenticated_user_id');
    }
}
