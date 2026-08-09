<?php

namespace App\Enums;

enum RouteSegmentStatus: string
{
    case Planned = 'planned';
    case InProgress = 'in_progress';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Planned => 'Plánujeme',
            self::InProgress => 'Právě cestujeme',
            self::Completed => 'Dokončeno',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $status) => [
            $status->value => $status->label(),
        ])->all();
    }
}
