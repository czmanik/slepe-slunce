<?php

namespace App\Http\Controllers;

use App\Models\RoutePoint;
use App\Models\RouteSegment;
use App\Models\MapPhoto;
use App\Models\MemberLocation;
use App\Services\ExpeditionTracker;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class RouteController extends Controller
{
    public function __invoke(ExpeditionTracker $tracker): View
    {
        $points = RoutePoint::query()
            ->with(['post' => fn ($query) => $query->publiclyVisible()])
            ->ordered()
            ->get();
        $segments = RouteSegment::query()
            ->with([
                'fromPoint',
                'toPoint',
                'post' => fn ($query) => $query->publiclyVisible(),
            ])
            ->ordered()
            ->get();
        $active = $tracker->active();
        $position = $tracker->position($active);
        if (($position['source'] ?? null) === 'gps') {
            $position['latitude'] = round($position['latitude'], 3);
            $position['longitude'] = round($position['longitude'], 3);
        }
        $mapPoints = $points->map(fn (RoutePoint $point): array => [
            'name' => $point->name,
            'description' => $point->description,
            'latitude' => (float) $point->latitude,
            'longitude' => (float) $point->longitude,
            'status' => $point->status->value,
            'statusLabel' => $point->status->label(),
            'isGoal' => $point->is_goal,
            'image' => $point->cover_image ? asset('storage/'.$point->cover_image) : null,
            'imageAlt' => $point->cover_alt,
            'postUrl' => $point->post ? route('posts.show', $point->post) : null,
            'isActive' => $active instanceof RoutePoint && $active->is($point),
        ])->values();
        $mapSegments = $segments->map(fn (RouteSegment $segment): array => [
            'name' => $segment->name ?: $segment->fromPoint->name.' → '.$segment->toPoint->name,
            'description' => $segment->description,
            'fromName' => $segment->fromPoint->name,
            'toName' => $segment->toPoint->name,
            'transport' => $segment->transport_mode->value,
            'transportLabel' => $segment->transport_mode->label(),
            'transportIcon' => $segment->transport_mode->icon(),
            'status' => $segment->status->value,
            'statusLabel' => $segment->status->label(),
            'geometry' => $segment->geometry ?: [
                [(float) $segment->fromPoint->latitude, (float) $segment->fromPoint->longitude],
                [(float) $segment->toPoint->latitude, (float) $segment->toPoint->longitude],
            ],
            'distance' => $segment->distance_km ? number_format((float) $segment->distance_km, 1, ',', ' ').' km' : null,
            'duration' => $this->formatDuration($segment->displayDuration()),
            'departure' => $segment->displayDeparture()?->translatedFormat('j. n. Y H:i'),
            'image' => $segment->cover_image ? asset('storage/'.$segment->cover_image) : null,
            'imageAlt' => $segment->cover_alt,
            'postUrl' => $segment->post ? route('posts.show', $segment->post) : null,
            'isActive' => $active instanceof RouteSegment && $active->is($segment),
        ])->values();
        $mapPhotos = MapPhoto::query()->with('user')->latest('taken_at')->get()->map(fn (MapPhoto $photo): array => [
            'latitude' => (float) $photo->latitude, 'longitude' => (float) $photo->longitude,
            'image' => asset('storage/'.$photo->image), 'alt' => $photo->alt, 'caption' => $photo->caption,
            'author' => $photo->user?->name, 'takenAt' => $photo->taken_at?->translatedFormat('j. n. Y H:i'),
        ])->values();
        $memberLocations = MemberLocation::query()->with('user')->get()->map(fn (MemberLocation $location): array => [
            'name' => str((string) $location->user?->name)->before(' ')->toString(),
            'latitude' => round((float) $location->latitude, 3), 'longitude' => round((float) $location->longitude, 3),
            'reportedAt' => $location->reported_at->translatedFormat('j. n. Y H:i'), 'age' => $location->reported_at->diffForHumans(),
            'stale' => $location->reported_at->lt(now()->subHours(6)),
        ])->values();

        $timeline = $this->buildTimeline($points, $segments);

        return view('route.index', compact('points', 'segments', 'mapPoints', 'mapSegments', 'mapPhotos', 'memberLocations', 'position', 'timeline'));
    }

    private function buildTimeline(Collection $points, Collection $segments): Collection
    {
        $timeline = collect();
        $segmentsByStart = $segments->groupBy('from_point_id');

        foreach ($points as $point) {
            $timeline->push(['type' => 'point', 'record' => $point]);

            foreach ($segmentsByStart->get($point->id, collect()) as $segment) {
                $timeline->push(['type' => 'segment', 'record' => $segment]);
            }
        }

        return $timeline;
    }

    private function formatDuration(?int $minutes): ?string
    {
        if ($minutes === null) {
            return null;
        }

        $hours = intdiv($minutes, 60);
        $remaining = $minutes % 60;

        return $hours > 0
            ? trim("{$hours} h ".($remaining ? "{$remaining} min" : ''))
            : "{$remaining} min";
    }
}
