<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscriber extends Model
{
    protected $fillable = [
        'email', 'name', 'status', 'new_expeditions', 'project_news', 'shop_news', 'confirm_token',
        'unsubscribe_token', 'confirmed_at', 'unsubscribed_at', 'consent_at', 'consent_ip',
        'source', 'mailchimp_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'new_expeditions' => 'boolean', 'project_news' => 'boolean', 'shop_news' => 'boolean',
            'confirmed_at' => 'datetime', 'unsubscribed_at' => 'datetime', 'consent_at' => 'datetime',
            'mailchimp_synced_at' => 'datetime',
        ];
    }

    public function expeditions(): BelongsToMany
    {
        return $this->belongsToMany(Expedition::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(ContentDelivery::class);
    }
}
