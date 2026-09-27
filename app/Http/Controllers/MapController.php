<?php

namespace App\Http\Controllers;

use App\Models\Expedition;
use App\Models\MapPhoto;
use App\Models\Post;
use App\Models\RoutePoint;
use App\Models\RouteSegment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MapController extends Controller
{
    public function __invoke(Request $request): View
    {
        $expeditions = Expedition::query()->published()->orderByDesc('start_at')->get();
        $selected = $request->query('expedition');
        $expedition = $selected ? $expeditions->firstWhere('slug', $selected) : null;
        abort_if($selected && ! $expedition, 404);
        $ids = $expedition ? [$expedition->id] : $expeditions->pluck('id')->all();
        $names = $expeditions->pluck('name', 'id');
        $items = collect();

        RoutePoint::query()->whereIn('expedition_id', $ids)->with(['post' => fn ($q) => $q->publiclyVisible()])->get()->each(function (RoutePoint $point) use ($items, $names): void {
            $items->push([
                'type' => 'places', 'name' => $point->name, 'expedition' => $names[$point->expedition_id] ?? '',
                'date' => $point->occurred_at?->toIso8601String(), 'dateLabel' => $point->occurred_at?->translatedFormat('j. n. Y H:i'),
                'latitude' => (float) $point->latitude, 'longitude' => (float) $point->longitude,
                'description' => $point->description, 'image' => $point->cover_image ? asset('storage/'.$point->cover_image) : null,
                'alt' => $point->cover_alt, 'url' => $point->post ? route('posts.show', $point->post) : null,
            ]);
        });
        Post::query()->publiclyVisible()->where(fn ($query) => $query->whereIn('expedition_id', $ids)
            ->when(! $expedition, fn ($query) => $query->orWhereNull('expedition_id')))
            ->whereNotNull('latitude')->whereNotNull('longitude')->get()->each(function (Post $post) use ($items, $names): void {
            $date = $post->journalDate();
            $items->push([
                'type' => 'articles', 'name' => $post->title, 'expedition' => $names[$post->expedition_id] ?? 'Projekt Slepé Slunce',
                'date' => $date?->toIso8601String(), 'dateLabel' => $date?->translatedFormat('j. n. Y'),
                'latitude' => (float) $post->latitude, 'longitude' => (float) $post->longitude,
                'description' => $post->excerpt, 'image' => $post->cover_image ? asset('storage/'.$post->cover_image) : null,
                'alt' => $post->cover_alt, 'url' => route('posts.show', $post),
            ]);
        });
        MapPhoto::query()->whereIn('expedition_id', $ids)->get()->each(function (MapPhoto $photo) use ($items, $names): void {
            $items->push([
                'type' => 'photos', 'name' => $photo->caption ?: $photo->alt, 'expedition' => $names[$photo->expedition_id] ?? '',
                'date' => $photo->taken_at?->toIso8601String(), 'dateLabel' => $photo->taken_at?->translatedFormat('j. n. Y H:i'),
                'latitude' => (float) $photo->latitude, 'longitude' => (float) $photo->longitude,
                'description' => $photo->short_story ?: $photo->caption, 'image' => asset('storage/'.$photo->image), 'alt' => $photo->alt, 'url' => null,
            ]);
        });
        $segments = RouteSegment::query()->whereIn('expedition_id', $ids)->with(['fromPoint', 'toPoint'])->ordered()->get()
            ->filter(fn (RouteSegment $segment) => $segment->fromPoint && $segment->toPoint
                && $segment->fromPoint->expedition_id === $segment->expedition_id
                && $segment->toPoint->expedition_id === $segment->expedition_id)
            ->map(fn (RouteSegment $segment) => [
                'geometry' => $segment->geometry ?: [
                    [(float) $segment->fromPoint->latitude, (float) $segment->fromPoint->longitude],
                    [(float) $segment->toPoint->latitude, (float) $segment->toPoint->longitude],
                ],
                'name' => $segment->name ?: $segment->fromPoint->name.' → '.$segment->toPoint->name,
                'transport' => $segment->transport_mode->label(), 'status' => $segment->status->value,
            ])->values();

        return view('map.index', [
            'expeditions' => $expeditions, 'selectedExpedition' => $expedition, 'expedition' => $expedition,
            'items' => $items->sortBy(fn (array $item) => $item['date'] ?? '9999')->values(), 'segments' => $segments,
        ]);
    }
}
