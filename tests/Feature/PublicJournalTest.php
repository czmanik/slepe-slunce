<?php

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Enums\UserRole;
use App\Models\Post;
use App\Models\User;
use App\Services\ImageThumbnail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicJournalTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_is_accessible_without_login(): void
    {
        $this->get('/')->assertOk()->assertSee('Přeskočit na hlavní obsah')->assertSee('Čtyři kamarádi');
    }

    public function test_public_layout_contains_analytics_and_partner_links(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('https://www.googletagmanager.com/gtag/js?id=G-REWW639R3N', escape: false)
            ->assertSee("gtag('config', 'G-REWW639R3N')", escape: false)
            ->assertSee('href="https://www.sons.cz/"', escape: false)
            ->assertSee('href="https://odskodnenizauraz.cz/"', escape: false)
            ->assertSee('SONS ČR')
            ->assertSee('Odškodnění za úraz');
    }

    public function test_only_published_posts_are_public(): void
    {
        $user = User::create(['name' => 'Editor', 'email' => 'editor@example.test', 'password' => 'password-password', 'role' => UserRole::Editor]);
        $published = Post::create(['created_by' => $user->id, 'title' => 'Veřejný zápis', 'slug' => 'verejny-zapis', 'excerpt' => 'Veřejný text.', 'body' => '<p>Obsah.</p>', 'status' => PostStatus::Published, 'published_at' => now(), 'cover_image' => 'test.jpg', 'cover_alt' => 'Testovací fotografie']);
        $draft = Post::create(['created_by' => $user->id, 'title' => 'Koncept', 'slug' => 'koncept', 'excerpt' => 'Skrytý text.', 'body' => '<p>Obsah.</p>', 'status' => PostStatus::Draft, 'cover_image' => 'test.jpg', 'cover_alt' => 'Testovací fotografie']);

        $this->get(route('posts.show', $published))->assertOk()->assertSee('Veřejný zápis');
        $this->get(route('posts.show', $published))->assertDontSee('article-media-summary', escape: false);
        $this->get('/denik/'.$draft->slug)->assertNotFound();
    }

    public function test_preview_requires_authentication(): void
    {
        $user = User::create(['name' => 'Autor', 'email' => 'autor@example.test', 'password' => 'password-password', 'role' => UserRole::Author]);
        $post = Post::create(['created_by' => $user->id, 'title' => 'Koncept', 'slug' => 'koncept', 'excerpt' => 'Text.', 'body' => '<p>Obsah.</p>', 'status' => PostStatus::Draft, 'cover_image' => 'test.jpg', 'cover_alt' => 'Test']);
        $this->get(route('posts.preview', $post))->assertRedirect();
        $this->actingAs($user)->get(route('posts.preview', $post))->assertOk()->assertSee('neveřejný náhled');
    }

    public function test_journal_is_chronological_and_can_be_filtered_by_expedition_day(): void
    {
        $user = User::create(['name' => 'Editor', 'email' => 'chronology@example.test', 'password' => 'password-password', 'role' => UserRole::Editor]);

        Post::create(['created_by' => $user->id, 'title' => 'Druhý den', 'excerpt' => 'Druhý den cesty.', 'body' => '<p>Obsah.</p>', 'status' => PostStatus::Published, 'published_at' => '2026-08-10 08:00:00', 'event_date' => '2026-08-11', 'cover_image' => 'second.jpg', 'cover_alt' => 'Druhý den']);
        Post::create(['created_by' => $user->id, 'title' => 'První den', 'excerpt' => 'První den cesty.', 'body' => '<p>Obsah.</p>', 'status' => PostStatus::Published, 'published_at' => '2026-08-12 08:00:00', 'event_date' => '2026-08-10', 'cover_image' => 'first.jpg', 'cover_alt' => 'První den']);

        $this->get(route('posts.index'))
            ->assertOk()
            ->assertSeeInOrder(['První den', 'Druhý den'])
            ->assertSee('day=2026-08-10', escape: false)
            ->assertSee('day=2026-08-11', escape: false);

        $this->get(route('posts.index', ['day' => '2026-08-11']))
            ->assertOk()
            ->assertSee('Druhý den')
            ->assertDontSee('První den');
    }

    public function test_article_header_links_to_present_media_only(): void
    {
        $user = User::create(['name' => 'Editor', 'email' => 'media@example.test', 'password' => 'password-password', 'role' => UserRole::Editor]);
        $post = Post::create([
            'created_by' => $user->id,
            'title' => 'Zápis s médii',
            'excerpt' => 'Dlouhý zápis s fotografiemi a videem.',
            'body' => '<p>Obsah.</p>',
            'status' => PostStatus::Published,
            'published_at' => now(),
            'cover_image' => 'cover.jpg',
            'cover_alt' => 'Titulní fotografie',
            'gallery' => [
                ['path' => 'gallery/one.jpg', 'alt' => 'První fotografie'],
                ['path' => 'gallery/two.jpg', 'alt' => 'Druhá fotografie'],
            ],
            'videos' => [['url' => 'https://youtu.be/abcdefghijk', 'title' => 'Video z cesty', 'description' => 'Popis videa']],
        ]);

        $this->get(route('posts.show', $post))
            ->assertOk()
            ->assertSee('2 fotografie')
            ->assertSee('1 video')
            ->assertSee('href="#fotografie"', escape: false)
            ->assertSee('href="#videa"', escape: false)
            ->assertSee('data-full-image', escape: false);
    }

    public function test_post_thumbnails_are_generated_when_gd_is_available(): void
    {
        if (! function_exists('imagecreatefromstring')) {
            $this->markTestSkipped('Rozšíření GD není dostupné.');
        }

        Storage::fake('public');
        $source = imagecreatetruecolor(20, 12);
        imagefill($source, 0, 0, imagecolorallocate($source, 244, 197, 66));
        ob_start();
        imagepng($source);
        $sourceBytes = ob_get_clean();
        imagedestroy($source);
        Storage::disk('public')->put('posts/covers/source.png', $sourceBytes);

        $thumbnails = app(ImageThumbnail::class);

        $this->assertTrue($thumbnails->generate('posts/covers/source.png'));
        Storage::disk('public')->assertExists($thumbnails->path('posts/covers/source.png', 'small'));
        Storage::disk('public')->assertExists($thumbnails->path('posts/covers/source.png', 'medium'));
        $this->assertStringContainsString('/storage/thumbnails/small/', $thumbnails->url('posts/covers/source.png', 'small'));
    }
}
