<?php

namespace App\Enums;

enum ExpeditionStatus: string
{
    case Planned = 'planned';
    case Active = 'active';
    case Completed = 'completed';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Planned => 'Plánovaná',
            self::Active => 'Právě probíhá',
            self::Completed => 'Dokončená',
            self::Archived => 'Archivní',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $status): array => [$status->value => $status->label()])->all();
    }
}
