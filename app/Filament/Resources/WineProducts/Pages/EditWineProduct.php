<?php

namespace App\Filament\Resources\WineProducts\Pages;

use App\Filament\Resources\WineProducts\WineProductResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWineProduct extends EditRecord
{
    protected static string $resource = WineProductResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
