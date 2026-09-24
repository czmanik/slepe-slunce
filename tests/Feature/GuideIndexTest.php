<?php

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuideIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_guides_are_grouped_by_topic_without_a_timeline(): void
    {
        $user = User::factory()->create();

        Post::query()->create([
            'created_by' => $user->id,
            'category' => Post::CATEGORY_TRAVEL,
            'guide_topic' => Post::GUIDE_TOPIC_TRANSPORT,
            'title' => 'Cesta vlakem',
            'slug' => 'cesta-vlakem',
            'excerpt' => 'Praktický návod.',
            'body' => '<p>Obsah.</p>',
            'status' => PostStatus::Published,
            'published_at' => now(),
        ]);

        $this->get(route('guides.index'))
            ->assertOk()
            ->assertSee('Doprava')
            ->assertSee('Cesta vlakem')
            ->assertSee('Chcete cestu zařídit společně?')
            ->assertDontSee('Nejnovější zápis');
    }
}
