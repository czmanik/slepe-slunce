<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ImportProductionContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_has_safe_preview_and_is_idempotent(): void
    {
        $root = sys_get_temp_dir().'/slepe-slunce-import-'.bin2hex(random_bytes(6));
        mkdir($root.'/source/posts/covers', 0755, true);
        mkdir($root.'/target', 0755, true);
        file_put_contents($root.'/source/posts/covers/cover.jpg', 'test-image');
        touch($root.'/source.sqlite');
        file_put_contents($root.'/.env', "DB_CONNECTION=sqlite\nDB_DATABASE={$root}/source.sqlite\n");

        config(['database.connections.import_test' => ['driver' => 'sqlite', 'database' => $root.'/source.sqlite']]);
        Schema::connection('import_test')->create('authors', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->text('bio')->nullable();
            $table->boolean('is_expedition_member')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
        });
        Schema::connection('import_test')->create('posts', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('slug');
            $table->text('excerpt');
            $table->longText('body');
            $table->string('status');
            $table->timestamp('published_at')->nullable();
            $table->date('event_date')->nullable();
            $table->string('location')->nullable();
            $table->string('cover_image')->nullable();
            $table->text('cover_alt')->nullable();
            $table->json('gallery')->nullable();
            $table->json('videos')->nullable();
            $table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();
            $table->softDeletes();
        });
        Schema::connection('import_test')->create('author_post', function (Blueprint $table): void {
            $table->unsignedBigInteger('author_id');
            $table->unsignedBigInteger('post_id');
            $table->unsignedInteger('sort_order')->default(0);
        });
        $authorId = DB::connection('import_test')->table('authors')->insertGetId([
            'name' => 'David', 'bio' => 'Autor deníku', 'is_expedition_member' => true, 'sort_order' => 1,
        ]);
        $postId = DB::connection('import_test')->table('posts')->insertGetId([
            'title' => 'První den', 'slug' => 'prvni-den', 'excerpt' => 'Začínáme.',
            'body' => '<p>Text cesty.</p>', 'status' => 'published', 'published_at' => '2026-08-10 12:00:00',
            'event_date' => '2026-08-10', 'location' => 'Praha', 'cover_image' => 'posts/covers/cover.jpg',
            'cover_alt' => 'Odjezd expedice', 'gallery' => '[]', 'videos' => '[]',
        ]);
        DB::connection('import_test')->table('author_post')->insert([
            'author_id' => $authorId, 'post_id' => $postId, 'sort_order' => 0,
        ]);

        $owner = User::create([
            'name' => 'Správce importu',
            'email' => 'owner@example.test',
            'password' => 'test-password',
            'role' => 'admin',
            'is_active' => true,
        ]);
        $arguments = [
            '--source-env' => $root.'/.env',
            '--source-storage' => $root.'/source',
            '--target-storage' => $root.'/target',
            '--owner' => $owner->email,
        ];

        $this->artisan('app:import-production-content', $arguments)->assertSuccessful();
        $this->assertDatabaseMissing('posts', ['slug' => 'prvni-den']);

        $this->artisan('app:import-production-content', $arguments + ['--apply' => true])->assertSuccessful();
        $this->artisan('app:import-production-content', $arguments + ['--apply' => true])->assertSuccessful();

        $post = Post::where('slug', 'prvni-den')->firstOrFail();
        $this->assertSame($owner->id, $post->created_by);
        $this->assertSame('slepe-slunce-2026', $post->expedition->slug);
        $this->assertSame(['David'], $post->authors->pluck('name')->all());
        $this->assertSame(1, Post::where('slug', 'prvni-den')->count());
        $this->assertFileExists($root.'/target/posts/covers/cover.jpg');
    }
}
