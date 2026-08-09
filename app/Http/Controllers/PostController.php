<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(): View
    {
        return view('posts.index', ['posts' => Post::publiclyVisible()->with('authors')->latest('published_at')->paginate(9)]);
    }

    public function show(Post $post): View
    {
        abort_unless(Post::publiclyVisible()->whereKey($post->getKey())->exists(), 404);

        return view('posts.show', ['post' => $post->load('authors'), 'preview' => false]);
    }

    public function preview(Post $post): View
    {
        $this->authorize('view', $post);

        return view('posts.show', ['post' => $post->load('authors'), 'preview' => true]);
    }
}
