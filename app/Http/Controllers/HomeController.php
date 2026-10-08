<?php

namespace App\Http\Controllers;

use App\Models\Expedition;
use App\Models\Post;
use App\Services\LatestExpeditionContent;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(LatestExpeditionContent $latest): View
    {
        $featuredExpedition = Expedition::published()->where('is_featured', true)->orderByDesc('start_at')->first();
        $expeditions = Expedition::published()
            ->where(fn ($query) => $query->whereNull('end_at')->orWhere('end_at', '>=', now()))
            ->orderBy('start_at')
            ->limit(3)
            ->get();
        $posts = Post::publiclyVisible()->with(['authors', 'expedition'])->latest('published_at')->limit(3)->get();
        $latestContent = $latest->forExpedition(limit: 6);

        return view('home', compact('posts', 'featuredExpedition', 'expeditions', 'latestContent'));
    }
}
