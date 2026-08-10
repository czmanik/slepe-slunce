<?php

namespace Tests\Feature;

use App\Enums\RoutePointStatus;
use App\Enums\RouteSegmentStatus;
use App\Enums\TransportMode;
use App\Models\Author;
use App\Models\MapPhoto;
use App\Models\RoutePoint;
use App\Models\RouteSegment;
use App\Models\User;
use App\Services\ExpeditionTracker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExpeditionTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_location_report_replaces_previous_location_of_member(): void
    {
        $user = $this->user();
        $this->actingAs($user)->post(route('tracking.location.store'), ['latitude' => 50.1, 'longitude' => 14.2, 'accuracy_meters' => 12])->assertRedirect();
        $this->actingAs($user)->post(route('tracking.location.store'), ['latitude' => 41.3, 'longitude' => 2.1, 'accuracy_meters' => 20])->assertRedirect();
        $this->assertDatabaseCount('member_locations', 1);
        $this->assertDatabaseHas('member_locations', ['user_id' => $user->id, 'latitude' => 41.3]);
    }

    public function test_manual_active_item_is_unique_across_point_and_segment(): void
    {
        $user = $this->user();
        $from = RoutePoint::query()->create(['name' => 'Praha', 'latitude' => 50.1, 'longitude' => 14.2, 'status' => RoutePointStatus::Current]);
        $to = RoutePoint::query()->create(['name' => 'Barcelona', 'latitude' => 41.3, 'longitude' => 2.1]);
        $segment = RouteSegment::query()->create(['from_point_id' => $from->id, 'to_point_id' => $to->id, 'transport_mode' => TransportMode::Flight, 'status' => RouteSegmentStatus::Planned]);
        app(ExpeditionTracker::class)->setActive($segment, $user->id);
        $this->assertSame(RoutePointStatus::Visited, $from->fresh()->status);
        $this->assertSame(RouteSegmentStatus::InProgress, $segment->fresh()->status);
        $this->assertTrue(app(ExpeditionTracker::class)->active()->is($segment));
    }

    public function test_map_photo_is_published_immediately_and_can_be_deleted_with_file(): void
    {
        Storage::fake('public'); $user = $this->user();
        RoutePoint::query()->create(['name' => 'Letiště', 'latitude' => 50.1, 'longitude' => 14.2, 'status' => RoutePointStatus::Current]);
        $this->actingAs($user)->post(route('tracking.photo.store'), ['image' => UploadedFile::fake()->image('airport.jpg'), 'alt' => 'Členové čekají u odletové tabule'])->assertRedirect();
        $photo = MapPhoto::query()->firstOrFail(); Storage::disk('public')->assertExists($photo->image);
        $path = $photo->image; $photo->delete(); Storage::disk('public')->assertMissing($path);
    }

    public function test_members_page_only_lists_expedition_members_with_photos(): void
    {
        Author::query()->create(['name' => 'Mirek', 'bio' => 'Člen výpravy.', 'photo' => 'members/mirek.jpg', 'photo_alt' => 'Mirek před odletem', 'is_expedition_member' => true]);
        Author::query()->create(['name' => 'Nejede', 'is_expedition_member' => false]);
        $this->get(route('members.index'))->assertOk()->assertSee('Mirek')->assertSee('Mirek před odletem')->assertDontSee('Nejede');
    }

    private function user(): User
    {
        return User::query()->create(['name' => 'Člen expedice', 'email' => fake()->unique()->safeEmail(), 'password' => 'bezpecne-heslo-123', 'role' => 'author', 'is_active' => true]);
    }
}
