<?php

namespace Tests\Feature;

use App\Enums\RoutePointStatus;
use App\Enums\RouteSegmentStatus;
use App\Enums\TransportMode;
use App\Models\RoutePoint;
use App\Models\RouteSegment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicRouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_route_page_has_accessible_empty_state(): void
    {
        $this->get(route('route.index'))
            ->assertOk()
            ->assertSee('Trasu právě připravujeme')
            ->assertSee('Trasa', escape: false);
    }

    public function test_route_points_are_displayed_in_route_order(): void
    {
        RoutePoint::query()->create([
            'name' => 'Druhý bod', 'latitude' => 41.3873974, 'longitude' => 2.1685680,
            'route_order' => 20, 'status' => RoutePointStatus::Planned,
        ]);
        RoutePoint::query()->create([
            'name' => 'První bod', 'latitude' => 50.0755381, 'longitude' => 14.4378005,
            'route_order' => 10, 'status' => RoutePointStatus::Visited,
        ]);

        $this->get(route('route.index'))
            ->assertOk()
            ->assertSeeInOrder(['První bod', 'Druhý bod'])
            ->assertSee('Interaktivní mapa trasy expedice')
            ->assertSee('Chronologicky a bez mapy');
    }

    public function test_only_one_point_can_be_current(): void
    {
        $first = RoutePoint::query()->create([
            'name' => 'Předchozí poloha', 'latitude' => 48.2081743, 'longitude' => 16.3738189,
            'route_order' => 10, 'status' => RoutePointStatus::Current,
        ]);
        RoutePoint::query()->create([
            'name' => 'Aktuální poloha', 'latitude' => 41.3873974, 'longitude' => 2.1685680,
            'route_order' => 20, 'status' => RoutePointStatus::Current,
        ]);

        $this->assertSame(RoutePointStatus::Visited, $first->fresh()->status);
        $this->assertSame(1, RoutePoint::query()->where('status', RoutePointStatus::Current->value)->count());
    }

    public function test_route_page_displays_transport_segment_in_accessible_timeline(): void
    {
        $prague = RoutePoint::query()->create([
            'name' => 'Praha – letiště', 'latitude' => 50.1008333, 'longitude' => 14.2600000,
            'route_order' => 10, 'status' => RoutePointStatus::Visited,
        ]);
        $barcelona = RoutePoint::query()->create([
            'name' => 'Barcelona – letiště', 'latitude' => 41.2974450, 'longitude' => 2.0832941,
            'route_order' => 20, 'status' => RoutePointStatus::Planned,
        ]);
        RouteSegment::query()->create([
            'from_point_id' => $prague->id,
            'to_point_id' => $barcelona->id,
            'name' => 'Let Praha → Barcelona',
            'transport_mode' => TransportMode::Flight,
            'status' => RouteSegmentStatus::Planned,
            'sort_order' => 10,
            'scheduled_departure_at' => '2026-08-10 08:30:00',
            'scheduled_arrival_at' => '2026-08-10 11:00:00',
            'distance_km' => 1358.4,
            'geometry' => [[50.1008333, 14.2600000], [41.2974450, 2.0832941]],
        ]);

        $this->get(route('route.index'))
            ->assertOk()
            ->assertSeeInOrder(['Praha – letiště', 'Let Praha → Barcelona', 'Barcelona – letiště'])
            ->assertSee('Letadlo')
            ->assertSee('1 358,4 km')
            ->assertSee('Časová osa cesty')
            ->assertSee('Interaktivní mapa zastávek a přesunů expedice');
    }
}
