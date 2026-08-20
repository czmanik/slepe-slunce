<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentDelivery extends Model
{
    protected $fillable = ['post_id', 'subscriber_id', 'frequency', 'sent_at', 'status', 'error'];

    protected function casts(): array
    {
        return ['sent_at' => 'datetime'];
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(Subscriber::class);
    }
}
