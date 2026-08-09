<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('home', ['posts' => Post::publiclyVisible()->with('authors')->latest('published_at')->limit(3)->get()]);
    }
}
