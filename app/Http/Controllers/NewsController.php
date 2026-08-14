<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\News;

class NewsController extends Controller
{
    //take last 10 news from news with moods
    public function getNews() {
        $news = News::query()
            ->where('status', 'mod')
            ->latest('published_at')
            ->take(10)
            ->with('moods')
            ->get();

        return view('index', [
            'news' => $news,
        ]);
    }
}
