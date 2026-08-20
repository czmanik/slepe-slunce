<?php

namespace App\Http\Controllers;

use App\Models\Expedition;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(Request $request, ?Expedition $expedition = null): View
    {
        $days = Post::publiclyVisible()
            ->when($expedition, fn ($query) => $query->whereBelongsTo($expedition))
            ->chronological()
            ->get(['event_date', 'published_at'])
            ->map(fn (Post $post): ?string => $post->journalDateKey())
            ->filter()
            ->unique()
            ->values();

        $selectedDay = $request->string('day')->toString();
        $selectedDay = $days->contains($selectedDay) ? $selectedDay : null;

        $posts = Post::publiclyVisible()
            ->when($expedition, fn ($query) => $query->whereBelongsTo($expedition))
            ->with('authors')
            ->when($selectedDay, fn ($query) => $query->where(function ($query) use ($selectedDay): void {
                $query->whereDate('event_date', $selectedDay)
                    ->orWhere(function ($query) use ($selectedDay): void {
                        $query->whereNull('event_date')->whereDate('published_at', $selectedDay);
                    });
            }))
            ->chronological()
            ->get();

        return view('posts.index', compact('posts', 'days', 'selectedDay', 'expedition'));
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
