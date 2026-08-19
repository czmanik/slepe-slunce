<?php

namespace App\Filament\Resources\WineProducts\Pages;

use App\Filament\Resources\WineProducts\WineProductResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWineProducts extends ListRecords
{
    protected static string $resource = WineProductResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
