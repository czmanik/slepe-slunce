<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpeditionMember extends Model
{
    public $timestamps = false;

    protected $table = 'author_expedition';

    protected $fillable = ['author_id', 'expedition_id', 'role', 'expedition_bio', 'is_leader', 'sort_order'];

    protected function casts(): array
    {
        return ['is_leader' => 'boolean', 'sort_order' => 'integer'];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    public function expedition(): BelongsTo
    {
        return $this->belongsTo(Expedition::class);
    }
}
