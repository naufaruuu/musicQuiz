<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Artist extends Model
{
    use HasFactory;

    protected $fillable = ['id', 'name'];
    public $incrementing = false; // Deezer uses custom IDs

    public function albums()
    {
        return $this->hasMany(Album::class);
    }
}
