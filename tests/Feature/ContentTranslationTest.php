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
        config()->set('services.libretranslate.url', 'http://127.0.0.1:5000');
        Http::fake(function ($request) {
            $translations = [
                'Cesta spolu' => 'A journey together',
                'Krátký český úvod.' => 'A short English introduction.',
                '<p>Český příběh.</p>' => '<p>English story.</p>',
                'Estepona' => 'Estepona',
                'Kamarádi u moře' => 'Friends by the sea',
            ];
            return Http::response(['translatedText' => $translations[$request['q']] ?? 'Translated']);
        });

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
        $this->assertFalse(app(ContentTranslationService::class)->translate($post->fresh()));

        $translation = $post->contentTranslations()->firstOrFail();
        $translation->update(['reviewed_at' => now()]);
        $post->update(['title' => 'Nový název']);
        $this->assertFalse(app(ContentTranslationService::class)->translate($post->fresh()));
        $this->assertTrue(app(ContentTranslationService::class)->translate($post->fresh(), 'en', true));
        $this->assertNull($translation->fresh()->reviewed_at);
    }
}
