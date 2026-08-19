<?php

namespace App\Filament\Widgets;

use App\Models\Expedition;
use App\Models\MemberLocation;
use App\Models\RoutePoint;
use App\Models\RouteSegment;
use App\Services\ExpeditionTracker;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;

class ExpeditionDashboard extends Widget
{
    protected string $view = 'filament.widgets.expedition-dashboard';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = -10;

    public function activatePoint(int $id): void
    {
        $this->activate(RoutePoint::query()->findOrFail($id));
    }

    public function activateSegment(int $id): void
    {
        $this->activate(RouteSegment::query()->findOrFail($id));
    }

    public function useAutomatic(): void
    {
        app(ExpeditionTracker::class)->automatic(Expedition::default());
        Notification::make()->success()->title('Aktivní etapa se nyní určuje podle času')->send();
    }

    protected function getViewData(): array
    {
        $tracker = app(ExpeditionTracker::class);
        $expedition = Expedition::default();
        $active = $tracker->active(null, $expedition);
        $points = RoutePoint::query()->whereBelongsTo($expedition)->ordered()->get();
        $segments = RouteSegment::query()->whereBelongsTo($expedition)->with(['fromPoint', 'toPoint'])->ordered()->get();
        $items = collect();
        $byStart = $segments->groupBy('from_point_id');
        foreach ($points as $point) {
            $items->push(['type' => 'point', 'record' => $point]);
            foreach ($byStart->get($point->id, collect()) as $segment) {
                $items->push(['type' => 'segment', 'record' => $segment]);
            }
        }

        return ['expedition' => $expedition, 'active' => $active, 'position' => $tracker->position($active, null, $expedition), 'items' => $items, 'state' => $tracker->state($expedition),
            'locations' => MemberLocation::query()->whereBelongsTo($expedition)->with('user')->latest('reported_at')->get()];
    }

    private function activate(RoutePoint|RouteSegment $record): void
    {
        app(ExpeditionTracker::class)->setActive($record, auth()->id());
        Notification::make()->success()->title('Aktivní etapa byla změněna')->send();
    }
}
