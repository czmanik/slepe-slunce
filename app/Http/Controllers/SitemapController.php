<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $posts = Post::publiclyVisible()->latest('updated_at')->get(['slug', 'updated_at']);
        return response()->view('sitemap', compact('posts'))->header('Content-Type', 'application/xml');
    }
}
