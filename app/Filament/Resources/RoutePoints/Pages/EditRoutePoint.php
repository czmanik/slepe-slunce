<?php

namespace App\Filament\Resources\RoutePoints\Pages;

use App\Filament\Resources\RoutePoints\RoutePointResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRoutePoint extends EditRecord
{
    protected static string $resource = RoutePointResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
