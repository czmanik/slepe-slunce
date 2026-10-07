<?php

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Enums\UserRole;
use App\Models\Post;
use App\Models\User;
use App\Services\ContentTranslationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ContentTranslationTest extends TestCase
{
    use RefreshDatabase;

    public function test_machine_translation_is_stored_and_used_on_english_domain(): void
    {
        config()->set('services.google_translate.key', 'test-key');
        Http::fake([
            'translation.googleapis.com/*' => Http::response([
                'data' => ['translations' => [
                    ['translatedText' => 'A journey together'],
                    ['translatedText' => 'A short English introduction.'],
                    ['translatedText' => '<p>English story.</p>'],
                    ['translatedText' => 'Estepona'],
                    ['translatedText' => 'Friends by the sea'],
                ]],
            ]),
        ]);

        $user = User::create(['name' => 'Editor', 'email' => 'translation@example.test', 'password' => 'password-password', 'role' => UserRole::Editor]);
        $post = Post::create([
            'created_by' => $user->id, 'title' => 'Cesta spolu', 'slug' => 'cesta-spolu',
            'excerpt' => 'Krátký český úvod.', 'body' => '<p>Český příběh.</p>', 'location' => 'Estepona',
            'status' => PostStatus::Published, 'published_at' => now(), 'cover_image' => 'cover.jpg', 'cover_alt' => 'Kamarádi u moře',
        ]);

        app(ContentTranslationService::class)->translate($post);

        $this->assertDatabaseHas('content_translations', ['translatable_type' => Post::class, 'translatable_id' => $post->id, 'locale' => 'en']);
        app()->setLocale('en');
        $translated = $post->fresh()->load('contentTranslations');
        $this->assertSame('A journey together', $translated->title);
        $this->assertSame('<p>English story.</p>', $translated->body);
    }
}
