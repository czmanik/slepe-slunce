<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Author extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'bio', 'is_expedition_member', 'sort_order'];

    protected function casts(): array
    {
        return ['is_expedition_member' => 'boolean', 'sort_order' => 'integer'];
    }

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class)->withPivot('sort_order')->orderByPivot('sort_order');
    }
}
