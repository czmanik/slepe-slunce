<?php

namespace App\Enums;

enum TransportMode: string
{
    case Flight = 'flight';
    case Bus = 'bus';
    case Car = 'car';
    case Train = 'train';
    case Walk = 'walk';
    case Bicycle = 'bicycle';
    case Ferry = 'ferry';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Flight => 'Letadlo',
            self::Bus => 'Autobus',
            self::Car => 'Auto',
            self::Train => 'Vlak',
            self::Walk => 'Pěšky',
            self::Bicycle => 'Kolo',
            self::Ferry => 'Loď',
            self::Other => 'Jiný přesun',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Flight => '✈',
            self::Bus => '🚌',
            self::Car => '🚗',
            self::Train => '🚆',
            self::Walk => '🚶',
            self::Bicycle => '🚲',
            self::Ferry => '⛴',
            self::Other => '→',
        };
    }

    public function usesRoadRouting(): bool
    {
        return in_array($this, [self::Bus, self::Car], true);
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $mode) => [
            $mode->value => $mode->label(),
        ])->all();
    }
}
