<?php

namespace App\Services;

use App\Models\WineVariant;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ShopCart
{
    public function currency(): string
    {
        return session('shop.currency', 'CZK');
    }

    public function setCurrency(string $currency): void
    {
        session(['shop.currency' => $currency]);
    }

    public function quantities(): array
    {
        return session('shop.cart', []);
    }

    public function add(WineVariant $variant, int $quantity): void
    {
        if (! $variant->is_active || ! $variant->product->is_active || $variant->availableStock() < $quantity) {
            throw ValidationException::withMessages(['quantity' => 'Požadované množství není skladem.']);
        }$cart = $this->quantities();
        $cart[$variant->id] = min($variant->availableStock(), ($cart[$variant->id] ?? 0) + $quantity);
        session(['shop.cart' => $cart]);
    }

    public function remove(WineVariant $variant): void
    {
        $cart = $this->quantities();
        unset($cart[$variant->id]);
        session(['shop.cart' => $cart]);
    }

    public function clear(): void
    {
        session()->forget('shop.cart');
    }

    public function lines(): Collection
    {
        $currency = $this->currency();
        $quantities = $this->quantities();

        return WineVariant::query()->with('product')->whereIn('id', array_keys($quantities))->get()->map(function ($variant) use ($currency, $quantities) {
            $qty = (int) $quantities[$variant->id];

            return ['variant' => $variant, 'quantity' => $qty, 'unit_price' => $variant->price($currency), 'total' => ($variant->price($currency) ?? 0) * $qty];
        })->filter(fn ($line) => $line['unit_price'] !== null)->values();
    }

    public function total(): int
    {
        return (int) $this->lines()->sum('total');
    }
}
