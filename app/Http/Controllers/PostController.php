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
        $requestedCategory = $request->string('category')->toString();
        $category = array_key_exists($requestedCategory, Post::categoryOptions())
            ? $requestedCategory
            : Post::CATEGORY_JOURNAL;

        $expeditions = Expedition::query()
            ->published()
            ->whereHas('posts', fn ($query) => $query->publiclyVisible()->inCategory(Post::CATEGORY_JOURNAL))
            ->withCount(['posts' => fn ($query) => $query->publiclyVisible()->inCategory(Post::CATEGORY_JOURNAL)])
            ->orderByDesc('start_at')
            ->get();

        $days = Post::publiclyVisible()
            ->inCategory($category)
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
            ->inCategory($category)
            ->when($expedition, fn ($query) => $query->whereBelongsTo($expedition))
            ->with(['authors', 'expedition'])
            ->when($selectedDay, fn ($query) => $query->where(function ($query) use ($selectedDay): void {
                $query->whereDate('event_date', $selectedDay)
                    ->orWhere(function ($query) use ($selectedDay): void {
                        $query->whereNull('event_date')->whereDate('published_at', $selectedDay);
                    });
            }))
            ->when($expedition, fn ($query) => $query->chronological(), fn ($query) => $query->orderByRaw('COALESCE(event_date, published_at) desc')->orderByDesc('published_at')->orderByDesc('id'))
            ->get();

        return view('posts.index', compact('posts', 'days', 'selectedDay', 'expedition', 'expeditions', 'category'));
    }

    public function travel(Request $request): View
    {
        $request->merge(['category' => Post::CATEGORY_TRAVEL]);

        return $this->index($request);
    }

    public function show(Post $post): View|\Illuminate\Http\RedirectResponse
    {
        if ($post->category === Post::CATEGORY_TRAVEL) {
            return redirect()->route('guides.show', $post, 301);
        }

        abort_unless(Post::publiclyVisible()->whereKey($post->getKey())->exists(), 404);

        return view('posts.show', ['post' => $post->load('authors'), 'preview' => false]);
    }

    public function guide(Post $post): View|\Illuminate\Http\RedirectResponse
    {
        if ($post->category !== Post::CATEGORY_TRAVEL) {
            return redirect()->route('posts.show', $post);
        }

        abort_unless(Post::publiclyVisible()->whereKey($post->getKey())->exists(), 404);

        return view('posts.show', ['post' => $post->load('authors'), 'preview' => false]);
    }

    public function preview(Post $post): View
    {
        $this->authorize('view', $post);

        return view('posts.show', ['post' => $post->load('authors'), 'preview' => true]);
    }
}
