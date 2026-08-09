<?php

namespace App\Models;

use App\Enums\RoutePointStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoutePoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id', 'name', 'description', 'latitude', 'longitude', 'route_order',
        'status', 'is_goal', 'occurred_at', 'cover_image', 'cover_alt', 'gallery', 'videos',
    ];

    protected static function booted(): void
    {
        static::saved(function (RoutePoint $point): void {
            if ($point->status === RoutePointStatus::Current) {
                static::query()
                    ->whereKeyNot($point->getKey())
                    ->where('status', RoutePointStatus::Current->value)
                    ->update(['status' => RoutePointStatus::Visited->value]);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7', 'longitude' => 'decimal:7', 'route_order' => 'integer',
            'status' => RoutePointStatus::class, 'is_goal' => 'boolean', 'occurred_at' => 'datetime',
            'gallery' => 'array', 'videos' => 'array',
        ];
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function outgoingSegments(): HasMany
    {
        return $this->hasMany(RouteSegment::class, 'from_point_id');
    }

    public function incomingSegments(): HasMany
    {
        return $this->hasMany(RouteSegment::class, 'to_point_id');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('route_order')->orderBy('occurred_at')->orderBy('id');
    }
}
