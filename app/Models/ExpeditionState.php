<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ExpeditionState extends Model
{
    protected $fillable = ['active_type', 'active_id', 'is_manual', 'changed_by'];

    protected function casts(): array
    {
        return ['is_manual' => 'boolean'];
    }

    public function active(): MorphTo { return $this->morphTo(); }
    public function changedBy(): BelongsTo { return $this->belongsTo(User::class, 'changed_by'); }
}
