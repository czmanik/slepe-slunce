<?php

namespace App\Models;

use App\Enums\PostStatus;
use App\Services\ImageThumbnail;
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

        static::saved(function (Post $post): void {
            foreach ($post->thumbnailSourcePaths() as $path) {
                app(ImageThumbnail::class)->generate($path);
            }
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

    public function scopeChronological(Builder $query): Builder
    {
        return $query
            ->orderByRaw('COALESCE(event_date, published_at) asc')
            ->orderBy('published_at')
            ->orderBy('id');
    }

    public function journalDate(): ?\Illuminate\Support\Carbon
    {
        return $this->event_date ?? $this->published_at;
    }

    public function journalDateKey(): ?string
    {
        return $this->journalDate()?->toDateString();
    }

    public function photoCount(): int
    {
        return count($this->galleryPhotos());
    }

    public function videoCount(): int
    {
        return count($this->videoItems());
    }

    public function photoCountLabel(): string
    {
        return $this->countLabel($this->photoCount(), 'fotografie', 'fotografie', 'fotografií');
    }

    public function videoCountLabel(): string
    {
        return $this->countLabel($this->videoCount(), 'video', 'videa', 'videí');
    }

    /** @return list<string> */
    public function thumbnailSourcePaths(): array
    {
        return array_values(array_filter([
            $this->cover_image,
            ...array_map(fn (array $photo): ?string => $photo['path'] ?? null, $this->galleryPhotos()),
        ]));
    }

    /** @return list<array<string, mixed>> */
    public function galleryPhotos(): array
    {
        return array_values(array_filter($this->gallery ?? [], fn (mixed $photo): bool => is_array($photo) && ! empty($photo['path'])));
    }

    /** @return list<array<string, mixed>> */
    public function videoItems(): array
    {
        return array_values(array_filter($this->videos ?? [], fn (mixed $video): bool => is_array($video) && ! empty($video['url'])));
    }

    public function readingMinutes(): int
    {
        return max(1, (int) ceil(str_word_count(strip_tags($this->body)) / 180));
    }

    private function countLabel(int $count, string $one, string $few, string $many): string
    {
        $mod100 = $count % 100;
        $word = $count === 1 ? $one : (($count % 10 >= 2 && $count % 10 <= 4 && ($mod100 < 10 || $mod100 > 20)) ? $few : $many);

        return "{$count} {$word}";
    }
}
