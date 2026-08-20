<?php

namespace App\Models;

use App\Enums\ProgramItemKind;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ProgramItem extends Model
{
    protected $fillable = ['expedition_id', 'item_type', 'item_id', 'kind', 'title', 'description', 'starts_at', 'ends_at', 'sort_order', 'is_public'];

    protected function casts(): array
    {
        return ['kind' => ProgramItemKind::class, 'starts_at' => 'datetime', 'ends_at' => 'datetime', 'sort_order' => 'integer', 'is_public' => 'boolean'];
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('starts_at')->orderBy('id');
    }

    public function expedition(): BelongsTo
    {
        return $this->belongsTo(Expedition::class);
    }

    public function item(): MorphTo
    {
        return $this->morphTo();
    }
}
