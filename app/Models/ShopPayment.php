<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopPayment extends Model
{
    protected $fillable = ['shop_order_id', 'provider', 'transaction_id', 'status', 'amount', 'currency', 'redirect_url', 'provider_payload', 'confirmed_at'];

    protected function casts(): array
    {
        return ['provider_payload' => 'array', 'confirmed_at' => 'datetime'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(ShopOrder::class, 'shop_order_id');
    }
}
