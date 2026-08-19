<?php

namespace App\Models;

use App\Enums\ProgramItemKind;
use App\Enums\RouteSegmentStatus;
use App\Enums\TransportMode;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class RouteSegment extends Model
{
    use HasFactory;

    protected $fillable = [
        'expedition_id', 'from_point_id', 'to_point_id', 'post_id', 'name', 'description', 'transport_mode',
        'status', 'sort_order', 'scheduled_departure_at', 'scheduled_arrival_at',
        'departed_at', 'arrived_at', 'distance_km', 'duration_minutes', 'provider',
        'reference', 'geometry_mode', 'waypoints', 'geometry', 'cover_image', 'cover_alt',
        'gallery', 'videos',
    ];

    protected static function booted(): void
    {
        static::saving(function (RouteSegment $segment): void {
            if ((int) $segment->from_point_id === (int) $segment->to_point_id) {
                throw ValidationException::withMessages(['to_point_id' => 'Cílová zastávka musí být jiná než výchozí.']);
            }

            $from = RoutePoint::query()->find($segment->from_point_id);
            $to = RoutePoint::query()->find($segment->to_point_id);
            if (! $from || ! $to || ! $from->expedition_id || $from->expedition_id !== $to->expedition_id) {
                throw ValidationException::withMessages(['to_point_id' => 'Obě zastávky musí patřit stejné expedici.']);
            }
            if ($segment->expedition_id && (int) $segment->expedition_id !== (int) $from->expedition_id) {
                throw ValidationException::withMessages(['expedition_id' => 'Přesun musí patřit stejné expedici jako obě zastávky.']);
            }
            $segment->expedition_id = $from->expedition_id;
            if ($segment->scheduled_departure_at && $segment->scheduled_arrival_at && $segment->scheduled_arrival_at < $segment->scheduled_departure_at) {
                throw ValidationException::withMessages(['scheduled_arrival_at' => 'Příjezd nemůže být před odjezdem.']);
            }
        });

        static::saved(function (RouteSegment $segment): void {
            if (! Schema::hasTable('program_items')) {
                return;
            }
            $segment->loadMissing(['fromPoint', 'toPoint']);
            ProgramItem::query()->updateOrCreate(
                ['item_type' => $segment->getMorphClass(), 'item_id' => $segment->getKey()],
                [
                    'expedition_id' => $segment->expedition_id,
                    'kind' => ProgramItemKind::Transfer,
                    'title' => $segment->name ?: $segment->fromPoint->name.' → '.$segment->toPoint->name,
                    'description' => $segment->description,
                    'starts_at' => $segment->displayDeparture(),
                    'ends_at' => $segment->displayArrival(),
                    'sort_order' => $segment->sort_order ?? 0,
                    'is_public' => true,
                ],
            );
        });

        static::deleted(function (RouteSegment $segment): void {
            if (Schema::hasTable('program_items')) {
                ProgramItem::query()->where('item_type', $segment->getMorphClass())->where('item_id', $segment->getKey())->delete();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'transport_mode' => TransportMode::class,
            'status' => RouteSegmentStatus::class,
            'sort_order' => 'integer',
            'scheduled_departure_at' => 'datetime',
            'scheduled_arrival_at' => 'datetime',
            'departed_at' => 'datetime',
            'arrived_at' => 'datetime',
            'distance_km' => 'decimal:1',
            'duration_minutes' => 'integer',
            'waypoints' => 'array',
            'geometry' => 'array',
            'gallery' => 'array',
            'videos' => 'array',
        ];
    }

    public function fromPoint(): BelongsTo
    {
        return $this->belongsTo(RoutePoint::class, 'from_point_id');
    }

    public function expedition(): BelongsTo
    {
        return $this->belongsTo(Expedition::class);
    }

    public function toPoint(): BelongsTo
    {
        return $this->belongsTo(RoutePoint::class, 'to_point_id');
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('scheduled_departure_at')->orderBy('id');
    }

    public function displayDeparture(): mixed
    {
        return $this->departed_at ?? $this->scheduled_departure_at;
    }

    public function displayArrival(): mixed
    {
        return $this->arrived_at ?? $this->scheduled_arrival_at;
    }

    public function displayDuration(): ?int
    {
        if ($this->departed_at && $this->arrived_at) {
            return (int) $this->departed_at->diffInMinutes($this->arrived_at);
        }

        if ($this->scheduled_departure_at && $this->scheduled_arrival_at) {
            return (int) $this->scheduled_departure_at->diffInMinutes($this->scheduled_arrival_at);
        }

        return $this->duration_minutes;
    }
}
