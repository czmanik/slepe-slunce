<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Unpaid = 'unpaid';
    case Deposit = 'deposit';
    case Paid = 'paid';
    case Discount = 'discount';

    public function label(): string
    {
        return match ($this) {
            self::Unpaid => 'Nezaplaceno',
            self::Deposit => 'Zaplacena záloha',
            self::Paid => 'Zaplaceno',
            self::Discount => 'Sleva',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $status): array => [$status->value => $status->label()])->all();
    }
}
