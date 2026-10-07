<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\MapPhoto;
use App\Services\ImageThumbnail;
use Illuminate\Console\Command;

class GeneratePostThumbnails extends Command
{
    protected $signature = 'app:generate-post-thumbnails {--force : Přegenerovat i existující náhledy}';

    protected $description = 'Vytvoří malé a střední náhledy fotografií v deníku a na mapě';

    public function handle(ImageThumbnail $thumbnails): int
    {
        if (! $thumbnails->supported()) {
            $this->error('Náhledy nelze vytvořit. Nainstalujte a aktivujte PHP rozšíření GD.');

            return self::FAILURE;
        }

        $generated = 0;

        Post::query()->withTrashed()->eachById(function (Post $post) use ($thumbnails, &$generated): void {
            foreach ($post->thumbnailSourcePaths() as $path) {
                if ($thumbnails->generate($path, (bool) $this->option('force'))) {
                    $generated++;
                }
            }
        });
        MapPhoto::query()->eachById(function (MapPhoto $photo) use ($thumbnails, &$generated): void {
            if ($thumbnails->generate($photo->image, (bool) $this->option('force'))) {
                $generated++;
            }
        });

        $this->info("Náhledy jsou připravené pro {$generated} fotografií.");

        return self::SUCCESS;
    }
}
