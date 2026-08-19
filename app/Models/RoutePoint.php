<?php

namespace App\Models;

use App\Enums\ProgramItemKind;
use App\Enums\RoutePointStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class RoutePoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'expedition_id', 'location_id', 'post_id', 'name', 'description', 'latitude', 'longitude', 'route_order',
        'status', 'is_goal', 'occurred_at', 'cover_image', 'cover_alt', 'gallery', 'videos',
    ];

    protected static function booted(): void
    {
        static::creating(function (RoutePoint $point): void {
            $point->expedition_id ??= Expedition::default()->getKey();
        });

        static::saving(function (RoutePoint $point): void {
            if ($point->location_id && ($location = Location::query()->find($point->location_id))) {
                $point->latitude = $location->latitude;
                $point->longitude = $location->longitude;
                $point->name = $point->name ?: $location->name;
            }
        });

        static::saved(function (RoutePoint $point): void {
            if ($point->status === RoutePointStatus::Current) {
                static::query()
                    ->whereKeyNot($point->getKey())
                    ->where('expedition_id', $point->expedition_id)
                    ->where('status', RoutePointStatus::Current->value)
                    ->update(['status' => RoutePointStatus::Visited->value]);
            }

            if (Schema::hasTable('program_items')) {
                ProgramItem::query()->updateOrCreate(
                    ['item_type' => $point->getMorphClass(), 'item_id' => $point->getKey()],
                    [
                        'expedition_id' => $point->expedition_id,
                        'kind' => ProgramItemKind::Stop,
                        'title' => $point->name,
                        'description' => $point->description,
                        'starts_at' => $point->occurred_at,
                        'sort_order' => $point->route_order ?? 0,
                        'is_public' => true,
                    ],
                );
            }
        });

        static::deleted(function (RoutePoint $point): void {
            if (Schema::hasTable('program_items')) {
                ProgramItem::query()->where('item_type', $point->getMorphClass())->where('item_id', $point->getKey())->delete();
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

    public function expedition(): BelongsTo
    {
        return $this->belongsTo(Expedition::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
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
