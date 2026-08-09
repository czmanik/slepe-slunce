<?php

namespace App\Models;

use App\Enums\PostStatus;
use App\Support\HtmlSanitizer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'created_by', 'title', 'slug', 'excerpt', 'body', 'status', 'published_at',
        'event_date', 'location', 'cover_image', 'cover_alt', 'gallery', 'videos',
        'seo_title', 'seo_description',
    ];

    protected static function booted(): void
    {
        static::saving(function (Post $post): void {
            $post->slug = Str::slug($post->slug ?: $post->title);
            $post->body = app(HtmlSanitizer::class)->sanitize($post->body ?? '');
        });
    }

    protected function casts(): array
    {
        return [
            'status' => PostStatus::class,
            'published_at' => 'datetime',
            'event_date' => 'date',
            'gallery' => 'array',
            'videos' => 'array',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class)->withPivot('sort_order')->orderByPivot('sort_order');
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query->where('status', PostStatus::Published)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function readingMinutes(): int
    {
        return max(1, (int) ceil(str_word_count(strip_tags($this->body)) / 180));
    }
}
