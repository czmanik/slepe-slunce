<?php

namespace App\Enums;

enum RegistrationMode: string
{
    case Interest = 'interest';
    case Application = 'application';
    case Reservation = 'reservation';

    public function label(): string
    {
        return match ($this) {
            self::Interest => 'Nezávazný zájem',
            self::Application => 'Žádost o účast',
            self::Reservation => 'Rezervace místa',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $mode): array => [$mode->value => $mode->label()])->all();
    }
}
