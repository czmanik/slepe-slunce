<?php

namespace App\Enums;

enum RoutePointStatus: string
{
    case Planned = 'planned';
    case Current = 'current';
    case Visited = 'visited';

    public function label(): string
    {
        return match ($this) {
            self::Planned => 'Plánujeme',
            self::Current => 'Jsme tady',
            self::Visited => 'Navštíveno',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $status) => [
            $status->value => $status->label(),
        ])->all();
    }
}
