<?php

namespace App\Models;

use App\Enums\RouteSegmentStatus;
use App\Enums\TransportMode;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RouteSegment extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_point_id', 'to_point_id', 'post_id', 'name', 'description', 'transport_mode',
        'status', 'sort_order', 'scheduled_departure_at', 'scheduled_arrival_at',
        'departed_at', 'arrived_at', 'distance_km', 'duration_minutes', 'provider',
        'reference', 'geometry_mode', 'waypoints', 'geometry', 'cover_image', 'cover_alt',
        'gallery', 'videos',
    ];

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
