<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopOrderItem extends Model
{
    protected $fillable = ['shop_order_id', 'wine_variant_id', 'sku', 'name', 'quantity', 'unit_price', 'vat_rate', 'total'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(ShopOrder::class, 'shop_order_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(WineVariant::class, 'wine_variant_id');
    }
}
