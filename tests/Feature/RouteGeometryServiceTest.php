<?php

namespace Tests\Feature;

use App\Enums\RouteSegmentStatus;
use App\Enums\RoutePointStatus;
use App\Enums\TransportMode;
use App\Models\RoutePoint;
use App\Models\RouteSegment;
use App\Services\RouteGeometryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RouteGeometryServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_flight_is_stored_as_great_circle_without_external_request(): void
    {
        Http::fake();
        [$from, $to] = $this->points();
        $segment = RouteSegment::query()->create([
            'from_point_id' => $from->id,
            'to_point_id' => $to->id,
            'transport_mode' => TransportMode::Flight,
            'status' => RouteSegmentStatus::Planned,
            'geometry_mode' => 'automatic',
        ]);

        app(RouteGeometryService::class)->refresh($segment);

        $this->assertCount(65, $segment->fresh()->geometry);
        $this->assertGreaterThan(1000, (float) $segment->fresh()->distance_km);
        Http::assertNothingSent();
    }

    public function test_car_route_is_loaded_once_and_stored_as_geometry(): void
    {
        Http::fake([
            '*' => Http::response([
                'code' => 'Ok',
                'routes' => [[
                    'distance' => 210500,
                    'duration' => 9900,
                    'geometry' => ['coordinates' => [[14.26, 50.1008], [9.5, 47.0], [2.0833, 41.2974]]],
                ]],
            ]),
        ]);
        [$from, $to] = $this->points();
        $segment = RouteSegment::query()->create([
            'from_point_id' => $from->id,
            'to_point_id' => $to->id,
            'transport_mode' => TransportMode::Car,
            'status' => RouteSegmentStatus::Planned,
            'geometry_mode' => 'automatic',
        ]);

        app(RouteGeometryService::class)->refresh($segment);
        $segment->refresh();

        $this->assertCount(3, $segment->geometry);
        $this->assertSame('210.5', $segment->distance_km);
        $this->assertSame(165, $segment->duration_minutes);
        Http::assertSentCount(1);
    }

    private function points(): array
    {
        return [
            RoutePoint::query()->create(['name' => 'Praha', 'latitude' => 50.1008333, 'longitude' => 14.2600000, 'status' => RoutePointStatus::Visited]),
            RoutePoint::query()->create(['name' => 'Barcelona', 'latitude' => 41.2974450, 'longitude' => 2.0832941, 'status' => RoutePointStatus::Planned]),
        ];
    }
}
