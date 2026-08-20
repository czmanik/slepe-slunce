<?php

namespace App\Enums;

enum NotificationFrequency: string
{
    case None = 'none';
    case Weekly = 'weekly';
    case Urgent = 'urgent';

    public function label(): string
    {
        return match ($this) {
            self::None => 'Nerozesílat',
            self::Weekly => 'Zařadit do týdenního přehledu',
            self::Urgent => 'Zařadit do nejbližšího denního přehledu',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $frequency): array => [$frequency->value => $frequency->label()])->all();
    }
}
