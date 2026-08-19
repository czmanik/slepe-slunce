<?php

namespace App\Enums;

enum ProgramItemKind: string
{
    case Stop = 'stop';
    case Transfer = 'transfer';
    case Activity = 'activity';
    case Accommodation = 'accommodation';
    case Tasting = 'tasting';
    case Meal = 'meal';
    case FreeTime = 'free_time';

    public function label(): string
    {
        return match ($this) {
            self::Stop => 'Zastávka',
            self::Transfer => 'Přesun',
            self::Activity => 'Aktivita',
            self::Accommodation => 'Ubytování',
            self::Tasting => 'Ochutnávka',
            self::Meal => 'Jídlo',
            self::FreeTime => 'Volný program',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $kind): array => [$kind->value => $kind->label()])->all();
    }
}
