<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\NewsMood;

class News extends Model
{
    protected $fillable = [
        'api_id',
        'title',
        'text',
        'source',
        'facts',
        'status',
        'published_at',
    ];

    //hasMany
    public function moods() {
        return $this->hasMany(NewsMood::class);
    }
}
