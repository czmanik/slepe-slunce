<?php

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Enums\UserRole;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicJournalTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_is_accessible_without_login(): void
    {
        $this->get('/')->assertOk()->assertSee('Přeskočit na hlavní obsah')->assertSee('Čtyři kamarádi');
    }

    public function test_only_published_posts_are_public(): void
    {
        $user = User::create(['name' => 'Editor', 'email' => 'editor@example.test', 'password' => 'password-password', 'role' => UserRole::Editor]);
        $published = Post::create(['created_by' => $user->id, 'title' => 'Veřejný zápis', 'slug' => 'verejny-zapis', 'excerpt' => 'Veřejný text.', 'body' => '<p>Obsah.</p>', 'status' => PostStatus::Published, 'published_at' => now(), 'cover_image' => 'test.jpg', 'cover_alt' => 'Testovací fotografie']);
        $draft = Post::create(['created_by' => $user->id, 'title' => 'Koncept', 'slug' => 'koncept', 'excerpt' => 'Skrytý text.', 'body' => '<p>Obsah.</p>', 'status' => PostStatus::Draft, 'cover_image' => 'test.jpg', 'cover_alt' => 'Testovací fotografie']);

        $this->get(route('posts.show', $published))->assertOk()->assertSee('Veřejný zápis');
        $this->get('/denik/'.$draft->slug)->assertNotFound();
    }

    public function test_preview_requires_authentication(): void
    {
        $user = User::create(['name' => 'Autor', 'email' => 'autor@example.test', 'password' => 'password-password', 'role' => UserRole::Author]);
        $post = Post::create(['created_by' => $user->id, 'title' => 'Koncept', 'slug' => 'koncept', 'excerpt' => 'Text.', 'body' => '<p>Obsah.</p>', 'status' => PostStatus::Draft, 'cover_image' => 'test.jpg', 'cover_alt' => 'Test']);
        $this->get(route('posts.preview', $post))->assertRedirect();
        $this->actingAs($user)->get(route('posts.preview', $post))->assertOk()->assertSee('neveřejný náhled');
    }
}
