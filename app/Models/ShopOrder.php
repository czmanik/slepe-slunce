<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShopOrder extends Model
{
    protected $fillable = ['number', 'status', 'payment_status', 'currency', 'subtotal', 'shipping_total', 'grand_total', 'customer_name', 'email', 'phone', 'billing_street', 'billing_city', 'billing_postcode', 'billing_country', 'delivery_method', 'note', 'invoice_number', 'access_token', 'paid_at', 'consent_at'];

    protected function casts(): array
    {
        return ['paid_at' => 'datetime', 'consent_at' => 'datetime'];
    }

    public function getRouteKeyName(): string
    {
        return 'number';
    }

    public function items(): HasMany
    {
        return $this->hasMany(ShopOrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(ShopPayment::class);
    }
}
