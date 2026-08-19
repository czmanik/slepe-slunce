<?php

use App\Enums\PostStatus;
use App\Models\ContentDelivery;
use App\Models\ExpeditionRegistration;
use App\Models\MemberLocation;
use App\Models\Post;
use App\Models\RouteSegment;
use App\Models\ShopOrder;
use App\Models\Subscriber;
use App\Services\ComgateGateway;
use App\Services\RouteGeometryService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schedule;

Artisan::command('posts:publish-scheduled', function (): void {
    $count = Post::query()->where('status', PostStatus::Scheduled)->where('published_at', '<=', now())->update(['status' => PostStatus::Published]);
    $this->info("Publikováno: {$count}");
})->purpose('Publikuje naplánované příspěvky.');

Schedule::command('posts:publish-scheduled')->everyMinute()->withoutOverlapping();

Artisan::command('subscriptions:send {frequency}', function (string $frequency): void {
    abort_unless(in_array($frequency, ['urgent', 'weekly'], true), 422, 'Frekvence musí být urgent nebo weekly.');
    $posts = Post::publiclyVisible()->where('notification_frequency', $frequency)->whereNull('notification_sent_at')->with('expedition')->get();
    if ($posts->isEmpty()) {
        $this->info('Žádné příspěvky k rozeslání.');

        return;
    }
    $sent = 0;
    Subscriber::query()->where('status', 'active')->with('expeditions')->each(function (Subscriber $subscriber) use ($posts, $frequency, &$sent): void {
        $selected = $posts->filter(fn (Post $post) => $post->expedition_id
            ? $subscriber->project_news || $subscriber->expeditions->contains($post->expedition_id)
            : $subscriber->project_news);
        if ($selected->isEmpty()) {
            return;
        }
        $body = "Novinky projektu Slepé Slunce\n\n".$selected->map(fn (Post $post) => $post->title."\n".route('posts.show', $post))->join("\n\n")
            ."\n\nOdhlásit odběr: ".route('subscriptions.unsubscribe', $subscriber->unsubscribe_token);
        try {
            Mail::raw($body, fn ($message) => $message->to($subscriber->email)->subject($frequency === 'urgent' ? 'Aktuálně ze Slepého Slunce' : 'Týden se Slepým Sluncem'));
            foreach ($selected as $post) {
                ContentDelivery::query()->updateOrCreate(['post_id' => $post->id, 'subscriber_id' => $subscriber->id], ['frequency' => $frequency, 'status' => 'sent', 'sent_at' => now(), 'error' => null]);
            }
            $sent++;
        } catch (Throwable $exception) {
            report($exception);
            foreach ($selected as $post) {
                ContentDelivery::query()->updateOrCreate(['post_id' => $post->id, 'subscriber_id' => $subscriber->id], ['frequency' => $frequency, 'status' => 'failed', 'error' => $exception->getMessage()]);
            }
        }
    });
    Post::query()->whereKey($posts->modelKeys())->update(['notification_sent_at' => now()]);
    $this->info("Odesláno odběratelům: {$sent}");
})->purpose('Rozešle denní urgentní nebo týdenní souhrn.');

Schedule::command('subscriptions:send urgent')->dailyAt('08:00')->withoutOverlapping();
Schedule::command('subscriptions:send weekly')->mondays()->at('09:00')->withoutOverlapping();

Artisan::command('privacy:cleanup', function (ComgateGateway $gateway): void {
    $pending = Subscriber::query()->where('status', 'pending')->where('created_at', '<', now()->subMonth())->delete();
    $expired = ExpeditionRegistration::query()->where('status', 'approved')->where('payment_status', 'unpaid')->where('hold_expires_at', '<', now())->update(['status' => 'expired']);
    $locations = 0;
    MemberLocation::query()->with('expedition')->each(function (MemberLocation $location) use (&$locations): void {
        $expedition = $location->expedition;
        if (! $expedition || $expedition->archive_member_locations) {
            return;
        }
        $days = $expedition->location_retention_days ?? 30;
        if ($location->reported_at->lt(now()->subDays($days))) {
            $location->delete();
            $locations++;
        }
    });
    $shopOrders = 0;
    ShopOrder::query()->where('payment_status', 'unpaid')->where('status', 'new')->where('created_at', '<', now()->subDays(2))->with('items.variant')->each(function (ShopOrder $order) use ($gateway, &$shopOrders): void {
        $gateway->releaseReservations($order);
        $order->update(['status' => 'cancelled', 'payment_status' => 'cancelled']);
        $shopOrders++;
    });
    $this->info("Smazáno: {$pending} nepotvrzených odběrů, {$locations} starých poloh; vypršelo rezervací: {$expired}; uvolněno objednávek: {$shopOrders}.");
})->purpose('Prosadí retenční lhůty a ukončí nezaplacené rezervace.');

Schedule::command('privacy:cleanup')->dailyAt('02:30')->withoutOverlapping();

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
