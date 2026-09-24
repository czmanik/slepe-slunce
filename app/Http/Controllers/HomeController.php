<?php

namespace App\Http\Controllers;

use App\Models\Expedition;
use App\Models\Post;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $featuredExpedition = Expedition::published()->where('is_featured', true)->orderByDesc('start_at')->first();
        $expeditions = Expedition::published()
            ->where(fn ($query) => $query->whereNull('end_at')->orWhere('end_at', '>=', now()))
            ->orderBy('start_at')
            ->limit(3)
            ->get();
        $posts = Post::publiclyVisible()->with(['authors', 'expedition'])->latest('published_at')->limit(3)->get();

        return view('home', compact('posts', 'featuredExpedition', 'expeditions'));
    }
}
