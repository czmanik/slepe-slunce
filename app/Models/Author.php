<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Schema;

class Author extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saved(function (Author $author): void {
            if (! Schema::hasTable('author_expedition')) {
                return;
            }
            $expedition = Expedition::default();
            if ($author->is_expedition_member) {
                $author->expeditions()->syncWithoutDetaching([$expedition->getKey() => ['expedition_bio' => $author->bio, 'sort_order' => $author->sort_order ?? 0]]);
            }
        });
    }

    protected $fillable = ['name', 'bio', 'photo', 'photo_alt', 'is_expedition_member', 'sort_order'];

    protected function casts(): array
    {
        return ['is_expedition_member' => 'boolean', 'sort_order' => 'integer'];
    }

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class)->withPivot('sort_order')->orderByPivot('sort_order');
    }

    public function expeditions(): BelongsToMany
    {
        return $this->belongsToMany(Expedition::class)
            ->withPivot(['role', 'expedition_bio', 'is_leader', 'sort_order'])
            ->orderByPivot('sort_order');
    }
}
