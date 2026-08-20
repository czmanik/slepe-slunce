<?php

namespace App\Models;

use App\Enums\ExpeditionStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Expedition extends Model
{
    public static function default(): self
    {
        return static::query()
            ->where('is_featured', true)
            ->orderByDesc('start_at')
            ->first()
            ?? static::query()->orderByDesc('start_at')->firstOrFail();
    }

    protected $fillable = [
        'organizer_user_id', 'name', 'slug', 'short_description', 'description', 'start_at', 'end_at',
        'timezone', 'publication_status', 'status_override', 'is_featured', 'hero_image', 'hero_alt',
        'registration_enabled', 'allowed_registration_modes', 'allowed_payment_methods', 'capacity', 'public_capacity',
        'registration_opens_at', 'registration_closes_at', 'price_czk', 'price_eur',
        'reservation_hold_hours', 'leader_name', 'contact_email', 'contact_phone', 'departure_details',
        'transport_details', 'accommodation_details', 'accessibility_details', 'included_services',
        'cancellation_terms', 'minimum_participants', 'archive_member_locations',
        'location_retention_days', 'settings',
    ];

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime', 'end_at' => 'datetime', 'registration_opens_at' => 'datetime',
            'registration_closes_at' => 'datetime', 'is_featured' => 'boolean',
            'registration_enabled' => 'boolean', 'allowed_registration_modes' => 'array', 'allowed_payment_methods' => 'array',
            'capacity' => 'integer', 'public_capacity' => 'integer', 'price_czk' => 'decimal:2',
            'price_eur' => 'decimal:2', 'reservation_hold_hours' => 'integer',
            'minimum_participants' => 'integer', 'archive_member_locations' => 'boolean',
            'location_retention_days' => 'integer', 'settings' => 'array',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('publication_status', 'published');
    }

    public function status(): ExpeditionStatus
    {
        if ($this->status_override) {
            return ExpeditionStatus::from($this->status_override);
        }
        if ($this->end_at?->isPast()) {
            return ExpeditionStatus::Completed;
        }
        if ($this->start_at?->isPast() && $this->end_at?->isFuture()) {
            return ExpeditionStatus::Active;
        }

        return ExpeditionStatus::Planned;
    }

    public function acceptsRegistrations(): bool
    {
        if (! $this->registration_enabled || $this->publication_status !== 'published') {
            return false;
        }
        if ($this->registration_opens_at?->isFuture()) {
            return false;
        }
        if ($this->registration_closes_at?->isPast()) {
            return false;
        }

        return count($this->allowed_registration_modes ?? []) > 0;
    }

    public function reservedPlaces(): int
    {
        return (int) $this->registrations()
            ->whereIn('status', ['approved', 'confirmed'])
            ->where(fn (Builder $query) => $query->whereNull('hold_expires_at')->orWhere('hold_expires_at', '>', now()))
            ->sum('party_size');
    }

    public function availablePlaces(): ?int
    {
        $capacity = $this->public_capacity ?? $this->capacity;

        return $capacity === null ? null : max(0, $capacity - $this->reservedPlaces());
    }

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_user_id');
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function routePoints(): HasMany
    {
        return $this->hasMany(RoutePoint::class);
    }

    public function routeSegments(): HasMany
    {
        return $this->hasMany(RouteSegment::class);
    }

    public function programItems(): HasMany
    {
        return $this->hasMany(ProgramItem::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(ExpeditionRegistration::class);
    }

    public function mapPhotos(): HasMany
    {
        return $this->hasMany(MapPhoto::class);
    }

    public function memberLocations(): HasMany
    {
        return $this->hasMany(MemberLocation::class);
    }

    public function state(): HasOne
    {
        return $this->hasOne(ExpeditionState::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(Author::class)
            ->withPivot(['role', 'expedition_bio', 'is_leader', 'sort_order'])
            ->orderByPivot('sort_order');
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(ExpeditionMember::class);
    }

    public function subscribers(): BelongsToMany
    {
        return $this->belongsToMany(Subscriber::class);
    }
}
