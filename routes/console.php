<?php

use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\RouteSegment;
use App\Services\RouteGeometryService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('posts:publish-scheduled', function (): void {
    $count = Post::query()->where('status', PostStatus::Scheduled)->where('published_at', '<=', now())->update(['status' => PostStatus::Published]);
    $this->info("Publikováno: {$count}");
})->purpose('Publikuje naplánované příspěvky.');

Schedule::command('posts:publish-scheduled')->everyMinute()->withoutOverlapping();

Artisan::command('route:recalculate-geometries', function (RouteGeometryService $geometry): void {
    $success = 0;
    $failed = 0;

    RouteSegment::query()->with(['fromPoint', 'toPoint'])->ordered()->each(function (RouteSegment $segment) use ($geometry, &$success, &$failed): void {
        try {
            $geometry->refresh($segment);
            $this->line("✓ {$segment->fromPoint->name} → {$segment->toPoint->name}");
            $success++;
        } catch (Throwable $exception) {
            $this->warn("! {$segment->fromPoint->name} → {$segment->toPoint->name}: {$exception->getMessage()}");
            $failed++;
        }
    });

    $this->info("Hotovo: {$success} přepočítáno, {$failed} s orientační geometrií.");
})->purpose('Přepočítá a uloží geometrii všech úseků cesty.');
