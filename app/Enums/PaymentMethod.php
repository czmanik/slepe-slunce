<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case BankTransfer = 'bank_transfer';
    case Card = 'card';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Hotově na místě',
            self::BankTransfer => 'Bankovním převodem',
            self::Card => 'Platební kartou',
        };
    }

    public function available(): bool
    {
        return $this !== self::Card;
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $method): array => [$method->value => $method->label()])->all();
    }
}
