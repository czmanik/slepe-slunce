<?php

namespace Tests\Feature;

use App\Models\Expedition;
use App\Models\MapPhoto;
use App\Models\RoutePoint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicMapTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_map_shows_only_items_from_published_expeditions(): void
    {
        $published = Expedition::query()->create(['name' => 'Veřejná cesta', 'slug' => 'verejna-cesta', 'publication_status' => 'published']);
        $draft = Expedition::query()->create(['name' => 'Tajná cesta', 'slug' => 'tajna-cesta', 'publication_status' => 'draft']);
        RoutePoint::query()->create(['expedition_id' => $published->id, 'name' => 'Místo na mapě', 'latitude' => 48.1, 'longitude' => 16.2]);
        RoutePoint::query()->create(['expedition_id' => $draft->id, 'name' => 'Tajné místo', 'latitude' => 48.1, 'longitude' => 16.2]);
        MapPhoto::query()->create(['expedition_id' => $published->id, 'alt' => 'Fotka moře', 'image' => 'map/photos/test.jpg', 'short_story' => 'Dnes jsme dorazili k moři.', 'latitude' => 48.1, 'longitude' => 16.2]);

        $this->get(route('map.index'))->assertOk()->assertSee('Místo na mapě')->assertSee('Dnes jsme dorazili k moři.')->assertDontSee('Tajné místo');
        $this->get(route('map.index', ['expedition' => $published->slug]))->assertOk()->assertSee('Místo na mapě');
        $this->get(route('map.index', ['expedition' => $draft->slug]))->assertNotFound();
    }
}
