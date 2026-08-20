<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WineVariant extends Model
{
    protected $fillable = ['wine_product_id', 'sku', 'vintage', 'bottle_size', 'quality', 'alcohol_percent_x10', 'price_czk', 'price_eur', 'vat_rate', 'stock_quantity', 'reserved_quantity', 'is_active', 'expert_appraisals'];

    protected function casts(): array
    {
        return ['vintage' => 'integer', 'price_czk' => 'integer', 'price_eur' => 'integer', 'stock_quantity' => 'integer', 'reserved_quantity' => 'integer', 'is_active' => 'boolean', 'expert_appraisals' => 'array'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(WineProduct::class, 'wine_product_id');
    }

    public function availableStock(): int
    {
        return max(0, $this->stock_quantity - $this->reserved_quantity);
    }

    public function price(string $currency): ?int
    {
        return $currency === 'EUR' ? $this->price_eur : $this->price_czk;
    }
}
