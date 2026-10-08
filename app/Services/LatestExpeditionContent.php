<?php

namespace App\Services;

use App\Models\MapPhoto;
use App\Models\Post;
use App\Models\RoutePoint;
use Illuminate\Support\Collection;

class LatestExpeditionContent
{
    public function __construct(private readonly ImageThumbnail $thumbnails) {}

    public function forExpedition(?int $expeditionId = null, int $limit = 6): Collection
    {
        $scope = fn ($query) => $expeditionId
            ? $query->where('expedition_id', $expeditionId)
            : $query->whereHas('expedition', fn ($expedition) => $expedition->published());

        $photos = $scope(MapPhoto::query())->with('expedition')->latest('taken_at')->limit($limit)->get()->map(fn (MapPhoto $photo) => [
            'type' => 'Fotografie', 'title' => $photo->caption ?: $photo->alt,
            'description' => $photo->short_story, 'date' => $photo->taken_at ?? $photo->created_at,
            'image' => $this->thumbnails->url($photo->image, 'small'), 'alt' => $photo->alt,
            'url' => route('map.photos.show', $photo), 'expedition' => $photo->expedition?->name,
        ]);
        $points = $scope(RoutePoint::query())->with('expedition')->latest('occurred_at')->limit($limit)->get()->map(fn (RoutePoint $point) => [
            'type' => 'Místo', 'title' => $point->name, 'description' => $point->description,
            'date' => $point->occurred_at ?? $point->created_at, 'image' => $point->cover_image ? asset('storage/'.$point->cover_image) : null,
            'alt' => $point->cover_alt, 'url' => route('map.index', ['expedition' => $point->expedition?->slug]),
            'expedition' => $point->expedition?->name,
        ]);
        $posts = $scope(Post::publiclyVisible()->inCategory(Post::CATEGORY_JOURNAL))->with('expedition')->latest('published_at')->limit($limit)->get()->map(fn (Post $post) => [
            'type' => 'Článek', 'title' => $post->title, 'description' => $post->excerpt,
            'date' => $post->journalDate(), 'image' => $post->cover_image ? asset('storage/'.$post->cover_image) : null,
            'alt' => $post->cover_alt, 'url' => route('posts.show', $post), 'expedition' => $post->expedition?->name,
        ]);

        return $photos->concat($points)->concat($posts)->sortByDesc(fn ($item) => $item['date']?->timestamp ?? 0)->take($limit)->values();
    }
}
