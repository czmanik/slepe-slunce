<?php

namespace App\Enums;

enum RegistrationStatus: string
{
    case New = 'new';
    case Approved = 'approved';
    case Waitlisted = 'waitlisted';
    case Confirmed = 'confirmed';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::New => 'Nová',
            self::Approved => 'Schválená – čeká na úhradu',
            self::Waitlisted => 'Čekací listina',
            self::Confirmed => 'Potvrzená',
            self::Rejected => 'Zamítnutá',
            self::Cancelled => 'Zrušená',
            self::Expired => 'Propadlá rezervace',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $status): array => [$status->value => $status->label()])->all();
    }
}
