<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TerminalPayment extends Model
{
    protected $fillable = [
        'expedition_registration_id', 'created_by', 'transaction_id', 'reference', 'reason', 'status', 'amount', 'currency',
        'provider_payload', 'checked_at', 'paid_at', 'applied_at',
    ];

    protected function casts(): array
    {
        return ['provider_payload' => 'array', 'checked_at' => 'datetime', 'paid_at' => 'datetime', 'applied_at' => 'datetime'];
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(ExpeditionRegistration::class, 'expedition_registration_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
