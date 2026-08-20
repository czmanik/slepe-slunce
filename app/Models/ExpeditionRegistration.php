<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\RegistrationMode;
use App\Enums\RegistrationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpeditionRegistration extends Model
{
    protected $fillable = [
        'expedition_id', 'reviewed_by', 'mode', 'status', 'payment_status', 'payment_method', 'name', 'email',
        'phone', 'party_size', 'departure_choice', 'assistance_needs', 'dietary_needs', 'note',
        'amount_due', 'amount_paid', 'currency', 'discount_amount', 'discount_note',
        'hold_expires_at', 'reviewed_at', 'consent_at', 'consent_ip',
    ];

    protected function casts(): array
    {
        return [
            'mode' => RegistrationMode::class, 'status' => RegistrationStatus::class,
            'payment_status' => PaymentStatus::class, 'payment_method' => PaymentMethod::class, 'party_size' => 'integer',
            'amount_due' => 'decimal:2', 'amount_paid' => 'decimal:2', 'discount_amount' => 'decimal:2',
            'hold_expires_at' => 'datetime', 'reviewed_at' => 'datetime', 'consent_at' => 'datetime',
        ];
    }

    public function expedition(): BelongsTo
    {
        return $this->belongsTo(Expedition::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
