<?php

namespace App\Services;

use App\Enums\TransportMode;
use App\Models\RouteSegment;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class RouteGeometryService
{
    public function refresh(RouteSegment $segment, bool $overwriteDistance = false, bool $overwriteDuration = false): void
    {
        $segment->loadMissing(['fromPoint', 'toPoint']);

        if (! $segment->fromPoint || ! $segment->toPoint) {
            throw new RuntimeException('Úsek musí mít výchozí i cílový bod.');
        }

        if ($segment->geometry_mode === 'manual') {
            return;
        }

        $points = $this->controlPoints($segment);

        if ($segment->geometry_mode === 'direct') {
            $this->store($segment, $points, $this->polylineDistance($points), null, $overwriteDistance, $overwriteDuration);

            return;
        }

        if ($segment->transport_mode === TransportMode::Flight) {
            $arc = $this->greatCircle($points[0], $points[array_key_last($points)]);
            $this->store($segment, $arc, $this->polylineDistance($arc), null, $overwriteDistance, $overwriteDuration);

            return;
        }

        if ($segment->transport_mode->usesRoadRouting()) {
            try {
                $this->routeOnRoad($segment, $points, $overwriteDistance, $overwriteDuration);

                return;
            } catch (Throwable $exception) {
                $this->store($segment, $points, $this->polylineDistance($points), null, $overwriteDistance, $overwriteDuration);

                throw new RuntimeException('Silniční trasu se nepodařilo načíst. Úsek byl uložen jako přímá spojnice; výpočet můžete zopakovat později.', previous: $exception);
            }
        }

        $this->store($segment, $points, $this->polylineDistance($points), null, $overwriteDistance, $overwriteDuration);
    }

    private function routeOnRoad(RouteSegment $segment, array $points, bool $overwriteDistance, bool $overwriteDuration): void
    {
        $coordinates = collect($points)
            ->map(fn (array $point): string => $point[1].','.$point[0])
            ->implode(';');
        $baseUrl = rtrim((string) config('services.routing.base_url', 'https://router.project-osrm.org'), '/');

        $response = Http::acceptJson()
            ->timeout((int) config('services.routing.timeout', 12))
            ->retry(2, 250)
            ->get("{$baseUrl}/route/v1/driving/{$coordinates}", [
                'overview' => 'full',
                'geometries' => 'geojson',
                'steps' => 'false',
            ]);

        if (! $response->successful() || $response->json('code') !== 'Ok') {
            throw new RuntimeException('Směrovací služba nevrátila použitelnou trasu.');
        }

        $route = $response->json('routes.0');
        $geometry = collect($route['geometry']['coordinates'] ?? [])
            ->map(fn (array $coordinate): array => [(float) $coordinate[1], (float) $coordinate[0]])
            ->all();

        if (count($geometry) < 2) {
            throw new RuntimeException('Směrovací služba vrátila prázdnou trasu.');
        }

        $this->store(
            $segment,
            $geometry,
            isset($route['distance']) ? round(((float) $route['distance']) / 1000, 1) : null,
            isset($route['duration']) ? (int) round(((float) $route['duration']) / 60) : null,
            $overwriteDistance,
            $overwriteDuration,
        );
    }

    private function controlPoints(RouteSegment $segment): array
    {
        $points = [[(float) $segment->fromPoint->latitude, (float) $segment->fromPoint->longitude]];

        foreach ($segment->waypoints ?? [] as $waypoint) {
            if (isset($waypoint['latitude'], $waypoint['longitude'])) {
                $points[] = [(float) $waypoint['latitude'], (float) $waypoint['longitude']];
            }
        }

        $points[] = [(float) $segment->toPoint->latitude, (float) $segment->toPoint->longitude];

        return $points;
    }

    private function greatCircle(array $from, array $to, int $steps = 64): array
    {
        [$lat1, $lon1] = array_map('deg2rad', $from);
        [$lat2, $lon2] = array_map('deg2rad', $to);
        $delta = 2 * asin(sqrt(pow(sin(($lat2 - $lat1) / 2), 2) + cos($lat1) * cos($lat2) * pow(sin(($lon2 - $lon1) / 2), 2)));

        if ($delta < 0.000001) {
            return [$from, $to];
        }

        $result = [];
        for ($index = 0; $index <= $steps; $index++) {
            $fraction = $index / $steps;
            $a = sin((1 - $fraction) * $delta) / sin($delta);
            $b = sin($fraction * $delta) / sin($delta);
            $x = $a * cos($lat1) * cos($lon1) + $b * cos($lat2) * cos($lon2);
            $y = $a * cos($lat1) * sin($lon1) + $b * cos($lat2) * sin($lon2);
            $z = $a * sin($lat1) + $b * sin($lat2);
            $result[] = [rad2deg(atan2($z, sqrt($x * $x + $y * $y))), rad2deg(atan2($y, $x))];
        }

        return $result;
    }

    private function polylineDistance(array $points): float
    {
        $distance = 0.0;
        for ($index = 1, $count = count($points); $index < $count; $index++) {
            [$lat1, $lon1] = array_map('deg2rad', $points[$index - 1]);
            [$lat2, $lon2] = array_map('deg2rad', $points[$index]);
            $distance += 6371 * 2 * asin(sqrt(pow(sin(($lat2 - $lat1) / 2), 2) + cos($lat1) * cos($lat2) * pow(sin(($lon2 - $lon1) / 2), 2)));
        }

        return round($distance, 1);
    }

    private function store(RouteSegment $segment, array $geometry, ?float $distance, ?int $duration, bool $overwriteDistance, bool $overwriteDuration): void
    {
        $values = ['geometry' => $geometry];
        if (($overwriteDistance || $segment->distance_km === null) && $distance !== null) {
            $values['distance_km'] = $distance;
        }
        if (($overwriteDuration || $segment->duration_minutes === null) && $duration !== null) {
            $values['duration_minutes'] = $duration;
        }

        $segment->forceFill($values)->saveQuietly();
    }
}
