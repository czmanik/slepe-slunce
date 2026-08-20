<?php

namespace App\Console\Commands;

use Dotenv\Dotenv;
use Illuminate\Console\Command;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class ImportProductionContent extends Command
{
    protected $signature = 'app:import-production-content
        {--source-env= : Absolutní cesta k .env zdrojové instalace}
        {--source-storage= : Absolutní cesta ke storage/app/public zdrojové instalace}
        {--target-storage= : Cílové storage/app/public; používá se hlavně v testech}
        {--owner= : E-mail existujícího správce, který bude vlastníkem článků}
        {--expedition=slepe-slunce-2026 : Slug cílové expedice}
        {--apply : Skutečně uložit data; bez přepínače se zobrazí pouze náhled}';

    protected $description = 'Bezpečně přenese autory, články, jejich vazby a média ze starší instalace';

    public function handle(): int
    {
        try {
            $source = $this->connectToSource();
            $sourceStorage = $this->absoluteDirectory((string) $this->option('source-storage'), 'Zdrojové úložiště');
            $targetStorage = $this->option('target-storage')
                ? $this->absoluteDirectory((string) $this->option('target-storage'), 'Cílové úložiště')
                : storage_path('app/public');

            $ownerId = DB::table('users')->where('email', $this->option('owner'))->value('id');
            if (! $ownerId) {
                throw new RuntimeException('Cílový správce nebyl nalezen. Zadejte --owner=existujici@email.cz.');
            }

            $expeditionId = DB::table('expeditions')->where('slug', $this->option('expedition'))->value('id');
            if (! $expeditionId) {
                throw new RuntimeException('Cílová expedice nebyla nalezena.');
            }

            $authors = $source->table('authors')->orderBy('id')->get();
            $posts = $source->table('posts')->whereNull('deleted_at')->orderBy('id')->get();
            $links = $source->table('author_post')->orderBy('sort_order')->get();
            $assets = $this->assetPaths($authors, $posts);

            $this->components->info(sprintf(
                'Nalezeno: %d autorů, %d článků, %d vazeb a %d mediálních souborů.',
                $authors->count(), $posts->count(), $links->count(), count($assets)
            ));
            $this->table(['Slug článku', 'Stav', 'Datum'], $posts->map(fn (object $post): array => [
                $post->slug, $post->status, $post->published_at ?? '—',
            ])->all());

            if (! $this->option('apply')) {
                $this->warn('Náhled dokončen. Pro provedení přidejte --apply.');

                return self::SUCCESS;
            }

            $copied = $this->copyAssets($assets, $sourceStorage, $targetStorage);
            DB::transaction(function () use ($authors, $posts, $links, $ownerId, $expeditionId): void {
                $authorIds = [];
                foreach ($authors as $author) {
                    $values = [
                        'bio' => $author->bio,
                        'is_expedition_member' => $author->is_expedition_member,
                        'sort_order' => $author->sort_order,
                        'updated_at' => now(),
                    ];
                    foreach (['photo', 'photo_alt'] as $optional) {
                        if (property_exists($author, $optional) && Schema::hasColumn('authors', $optional)) {
                            $values[$optional] = $author->{$optional};
                        }
                    }
                    DB::table('authors')->updateOrInsert(['name' => $author->name], $values + ['created_at' => now()]);
                    $authorIds[$author->id] = DB::table('authors')->where('name', $author->name)->value('id');
                }

                $postIds = [];
                foreach ($posts as $post) {
                    $values = [
                        'created_by' => $ownerId,
                        'expedition_id' => $expeditionId,
                        'title' => $post->title,
                        'excerpt' => $post->excerpt,
                        'body' => $post->body,
                        'status' => $post->status,
                        'published_at' => $post->published_at,
                        'event_date' => $post->event_date,
                        'location' => $post->location,
                        'cover_image' => $post->cover_image,
                        'cover_alt' => $post->cover_alt,
                        'gallery' => $post->gallery,
                        'videos' => $post->videos,
                        'seo_title' => $post->seo_title,
                        'seo_description' => $post->seo_description,
                        'updated_at' => now(),
                        'deleted_at' => null,
                    ];
                    DB::table('posts')->updateOrInsert(['slug' => $post->slug], $values + ['created_at' => now()]);
                    $postIds[$post->id] = DB::table('posts')->where('slug', $post->slug)->value('id');
                }

                foreach ($postIds as $postId) {
                    DB::table('author_post')->where('post_id', $postId)->delete();
                }
                foreach ($links as $link) {
                    if (isset($authorIds[$link->author_id], $postIds[$link->post_id])) {
                        DB::table('author_post')->insert([
                            'author_id' => $authorIds[$link->author_id],
                            'post_id' => $postIds[$link->post_id],
                            'sort_order' => $link->sort_order,
                        ]);
                    }
                }
            });

            $this->components->info("Import dokončen. Zkopírováno {$copied} nových mediálních souborů.");
            $this->line('Náhledy vytvořte příkazem: php artisan app:generate-post-thumbnails');

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        } finally {
            DB::purge('production_import');
        }
    }

    private function connectToSource(): ConnectionInterface
    {
        $envPath = (string) $this->option('source-env');
        if (! str_starts_with($envPath, '/') || ! is_file($envPath) || ! is_readable($envPath)) {
            throw new RuntimeException('Zadejte čitelnou absolutní cestu pomocí --source-env=.');
        }
        $env = Dotenv::parse((string) file_get_contents($envPath));
        $driver = $env['DB_CONNECTION'] ?? 'mysql';
        if (! in_array($driver, ['mysql', 'mariadb', 'sqlite'], true)) {
            throw new RuntimeException('Zdrojový databázový ovladač není podporován.');
        }

        $config = $driver === 'sqlite'
            ? ['driver' => 'sqlite', 'database' => $env['DB_DATABASE'] ?? '']
            : [
                'driver' => $driver,
                'host' => $env['DB_HOST'] ?? '127.0.0.1',
                'port' => $env['DB_PORT'] ?? '3306',
                'database' => $env['DB_DATABASE'] ?? '',
                'username' => $env['DB_USERNAME'] ?? '',
                'password' => $env['DB_PASSWORD'] ?? '',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'strict' => true,
            ];
        config(['database.connections.production_import' => $config]);
        DB::purge('production_import');

        return DB::connection('production_import');
    }

    /** @return list<string> */
    private function assetPaths($authors, $posts): array
    {
        $paths = [];
        foreach ($authors as $author) {
            if (property_exists($author, 'photo') && $author->photo) {
                $paths[] = $author->photo;
            }
        }
        foreach ($posts as $post) {
            if ($post->cover_image) {
                $paths[] = $post->cover_image;
            }
            foreach (json_decode($post->gallery ?: '[]', true) ?: [] as $photo) {
                if (is_array($photo) && ! empty($photo['path'])) {
                    $paths[] = $photo['path'];
                }
            }
        }

        return array_values(array_unique($paths));
    }

    private function copyAssets(array $paths, string $sourceRoot, string $targetRoot): int
    {
        $copied = 0;
        foreach ($paths as $path) {
            $relative = ltrim(str_replace('\\', '/', (string) $path), '/');
            if ($relative === '' || str_contains('/'.$relative.'/', '/../')) {
                throw new RuntimeException("Nebezpečná cesta k médiu: {$path}");
            }
            $source = $sourceRoot.'/'.$relative;
            if (! is_file($source)) {
                throw new RuntimeException("Zdrojové médium neexistuje: {$relative}");
            }
            $target = $targetRoot.'/'.$relative;
            if (is_file($target)) {
                continue;
            }
            if (! is_dir(dirname($target)) && ! mkdir(dirname($target), 0755, true) && ! is_dir(dirname($target))) {
                throw new RuntimeException("Nelze vytvořit adresář pro médium: {$relative}");
            }
            if (! copy($source, $target)) {
                throw new RuntimeException("Médium se nepodařilo zkopírovat: {$relative}");
            }
            $copied++;
        }

        return $copied;
    }

    private function absoluteDirectory(string $path, string $label): string
    {
        if (! str_starts_with($path, '/') || ! is_dir($path)) {
            throw new RuntimeException("{$label} musí být existující absolutní adresář.");
        }

        return rtrim($path, '/');
    }
}
