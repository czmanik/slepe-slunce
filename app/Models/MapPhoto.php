<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class MapPhoto extends Model
{
    protected $fillable = ['expedition_id', 'user_id', 'route_point_id', 'route_segment_id', 'image', 'alt', 'caption', 'latitude', 'longitude', 'taken_at'];

    protected function casts(): array
    {
        return ['latitude' => 'decimal:7', 'longitude' => 'decimal:7', 'taken_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::deleted(fn (MapPhoto $photo) => Storage::disk('public')->delete($photo->image));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function expedition(): BelongsTo
    {
        return $this->belongsTo(Expedition::class);
    }

    public function routePoint(): BelongsTo
    {
        return $this->belongsTo(RoutePoint::class);
    }

    public function routeSegment(): BelongsTo
    {
        return $this->belongsTo(RouteSegment::class);
    }
}
