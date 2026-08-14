<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\News;

class NewsMood extends Model
{
    protected $fillable = [
        'news_id',
        'mood',
        'text',
        'facts',
    ];

    //BELONGS TO
    public function news()
    {
        return $this->belongsTo(News::class);
    }
}
