<?php

namespace App\Services;

use App\Enums\RouteSegmentStatus;
use App\Models\Expedition;
use App\Models\ExpeditionState;
use App\Models\MemberLocation;
use App\Models\RoutePoint;
use App\Models\RouteSegment;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ExpeditionTracker
{
    public function state(?Expedition $expedition = null): ExpeditionState
    {
        $expedition ??= Expedition::default();

        return ExpeditionState::query()->firstOrCreate(['expedition_id' => $expedition->getKey()]);
    }

    public function active(?CarbonInterface $now = null, ?Expedition $expedition = null): ?Model
    {
        $expedition ??= Expedition::default();
        $state = $this->state($expedition);
        if ($state->is_manual && $state->active) {
            return $state->active;
        }

        $now ??= now();
        $segment = RouteSegment::query()
            ->where('expedition_id', $expedition->getKey())
            ->where(fn ($q) => $q->where('status', RouteSegmentStatus::InProgress->value)
                ->orWhere(fn ($q) => $q->whereNotNull('scheduled_departure_at')->whereNotNull('scheduled_arrival_at')
                    ->where('scheduled_departure_at', '<=', $now)->where('scheduled_arrival_at', '>=', $now)))
            ->orderByRaw("status = 'in_progress' desc")
            ->orderBy('scheduled_departure_at')->first();
        if ($segment) {
            return $segment;
        }

        return RoutePoint::query()->where('expedition_id', $expedition->getKey())->where('status', 'current')->first()
            ?? RoutePoint::query()->where('expedition_id', $expedition->getKey())->whereNotNull('occurred_at')->where('occurred_at', '<=', $now)->ordered()->get()->last()
            ?? RoutePoint::query()->where('expedition_id', $expedition->getKey())->ordered()->first();
    }

    public function setActive(Model $record, int $userId): void
    {
        DB::transaction(function () use ($record, $userId): void {
            $expedition = $record->expedition ?? Expedition::default();
            RoutePoint::query()->where('expedition_id', $expedition->getKey())->where('status', 'current')->update(['status' => 'visited']);
            RouteSegment::query()->where('expedition_id', $expedition->getKey())->where('status', 'in_progress')->update(['status' => 'completed', 'arrived_at' => now()]);
            if ($record instanceof RoutePoint) {
                $record->update(['status' => 'current']);
            }
            if ($record instanceof RouteSegment) {
                $record->update(['status' => 'in_progress', 'departed_at' => $record->departed_at ?? now(), 'arrived_at' => null]);
            }
            $this->state($expedition)->forceFill([
                'active_type' => $record->getMorphClass(), 'active_id' => $record->getKey(),
                'is_manual' => true, 'changed_by' => $userId,
            ])->save();
        });
    }

    public function automatic(?Expedition $expedition = null): void
    {
        $this->state($expedition)->forceFill(['active_type' => null, 'active_id' => null, 'is_manual' => false, 'changed_by' => auth()->id()])->save();
    }

    public function position(?Model $active = null, ?CarbonInterface $now = null, ?Expedition $expedition = null): ?array
    {
        $expedition ??= $active?->expedition ?? Expedition::default();
        $active ??= $this->active($now, $expedition);
        $now ??= now();
        $fresh = MemberLocation::query()->where('expedition_id', $expedition->getKey())->latest('reported_at')->first();
        if ($fresh && $fresh->reported_at->gte($now->copy()->subHours(6))) {
            return ['latitude' => (float) $fresh->latitude, 'longitude' => (float) $fresh->longitude, 'source' => 'gps', 'reportedAt' => $fresh->reported_at];
        }
        if ($active instanceof RoutePoint) {
            return ['latitude' => (float) $active->latitude, 'longitude' => (float) $active->longitude, 'source' => 'point', 'reportedAt' => null];
        }
        if (! $active instanceof RouteSegment) {
            return null;
        }
        $active->loadMissing(['fromPoint', 'toPoint']);
        $geometry = $active->geometry ?: [[(float) $active->fromPoint->latitude, (float) $active->fromPoint->longitude], [(float) $active->toPoint->latitude, (float) $active->toPoint->longitude]];
        $start = $active->departed_at ?? $active->scheduled_departure_at;
        $end = $active->arrived_at ?? $active->scheduled_arrival_at;
        $progress = ($start && $end && $end->gt($start)) ? $start->diffInSeconds($now, false) / $start->diffInSeconds($end) : 0;
        $progress = max(0, min(1, $progress));
        $index = min(count($geometry) - 1, (int) round($progress * (count($geometry) - 1)));

        return ['latitude' => (float) $geometry[$index][0], 'longitude' => (float) $geometry[$index][1], 'source' => 'estimate', 'progress' => (int) round($progress * 100), 'reportedAt' => null];
    }
}
