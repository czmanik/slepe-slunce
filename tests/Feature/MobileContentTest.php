<?php

namespace Tests\Feature;

use App\Models\Expedition;
use App\Models\MapPhoto;
use App\Models\RoutePoint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MobileContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_photo_can_be_moved_and_edited_by_its_author_but_not_another_author(): void
    {
        $first = $this->expedition('prvni');
        $second = $this->expedition('druha');
        $author = $this->user('author');
        $other = $this->user('author');
        $photo = MapPhoto::create(['expedition_id' => $first->id, 'user_id' => $author->id, 'image' => 'map/photos/test.jpg',
            'alt' => 'Původní', 'latitude' => 50.1, 'longitude' => 14.4, 'taken_at' => now()]);
        $data = ['expedition_id' => $second->id, 'alt' => 'Nový popis', 'caption' => 'U moře',
            'latitude' => 36.4, 'longitude' => -5.1, 'taken_at' => '2026-10-02T12:30'];

        $this->actingAs($other)->patch(route('mobile.photos.update', $photo), $data)->assertForbidden();
        $this->actingAs($author)->patch(route('mobile.photos.update', $photo), $data)->assertRedirect(route('mobile.content.index'));
        $this->assertDatabaseHas('map_photos', ['id' => $photo->id, 'expedition_id' => $second->id, 'alt' => 'Nový popis']);
        $this->get(route('expeditions.show', $first))->assertDontSee('U moře');
        $this->get(route('expeditions.show', $second))->assertSee('U moře');
        $this->get(route('map.photos.show', $photo))->assertOk()->assertSee('Otevřít originál fotografie');
    }

    public function test_point_edit_requires_editor_and_updates_expedition_timeline(): void
    {
        $first = $this->expedition('prvni');
        $second = $this->expedition('druha');
        $point = RoutePoint::create(['expedition_id' => $first->id, 'name' => 'Původní místo', 'latitude' => 50.1, 'longitude' => 14.4]);
        $data = ['expedition_id' => $second->id, 'name' => 'Nové místo', 'latitude' => 36.4,
            'longitude' => -5.1, 'occurred_at' => '2026-10-02T12:30', 'status' => 'visited'];

        $this->actingAs($this->user('author'))->patch(route('mobile.points.update', $point), $data)->assertForbidden();
        $this->actingAs($this->user('editor'))->patch(route('mobile.points.update', $point), $data)->assertRedirect(route('mobile.content.index'));
        $this->assertDatabaseHas('route_points', ['id' => $point->id, 'expedition_id' => $second->id, 'name' => 'Nové místo']);
        $this->get(route('expeditions.show', $second))->assertSee('Nové místo');
    }

    public function test_unpublished_photo_is_not_exposed_and_release_manifest_is_only_shown_with_apk(): void
    {
        $draft = Expedition::create(['name' => 'Návrh', 'slug' => 'navrh', 'publication_status' => 'draft']);
        $photo = MapPhoto::create(['expedition_id' => $draft->id, 'image' => 'map/photos/test.jpg', 'alt' => 'Soukromá', 'latitude' => 50, 'longitude' => 14]);
        $this->get(route('map.photos.show', $photo))->assertNotFound();

        Storage::fake('local');
        Storage::fake('public');
        $this->get(route('app.version'))->assertOk()->assertJsonPath('version_code', 0);
        Storage::disk('local')->put('android-release.json', json_encode(['version_code' => 2, 'version_name' => '0.2.0', 'file' => 'app/slepe-slunce-v2.apk']));
        Storage::disk('public')->put('app/slepe-slunce-v2.apk', 'apk');
        $this->get(route('app.version'))->assertOk()->assertJsonPath('version_code', 2)
            ->assertJsonPath('download_url', secure_url('storage/app/slepe-slunce-v2.apk'));
    }

    private function expedition(string $slug): Expedition
    {
        return Expedition::create(['name' => $slug, 'slug' => $slug, 'publication_status' => 'published']);
    }

    private function user(string $role): User
    {
        return User::create(['name' => 'Tester', 'email' => fake()->unique()->safeEmail(), 'password' => 'password-password', 'role' => $role, 'is_active' => true]);
    }
}
