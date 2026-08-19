<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    protected $fillable = ['name', 'address', 'country_code', 'latitude', 'longitude'];

    protected function casts(): array
    {
        return ['latitude' => 'decimal:7', 'longitude' => 'decimal:7'];
    }

    public function stops(): HasMany
    {
        return $this->hasMany(RoutePoint::class);
    }
}
