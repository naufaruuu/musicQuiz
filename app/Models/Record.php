<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Record extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'artist_id', 'score'];

    // Relationship with Username
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    // Relationship with Artist
    public function artist()
    {
        return $this->belongsTo(\App\Models\Artist::class, 'artist_id');
    }
}
