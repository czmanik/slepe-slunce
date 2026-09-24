<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WineProduct extends Model
{
    protected $fillable = ['name', 'slug', 'winery', 'description', 'image', 'image_alt', 'is_active', 'is_archival'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'is_archival' => 'boolean'];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function variants(): HasMany
    {
        return $this->hasMany(WineVariant::class);
    }

    public function scopeAvailable(Builder $q): Builder
    {
        return $q->where('is_active', true)->whereHas('variants', fn ($q) => $q->where('is_active', true)->whereColumn('stock_quantity', '>', 'reserved_quantity'));
    }
}
