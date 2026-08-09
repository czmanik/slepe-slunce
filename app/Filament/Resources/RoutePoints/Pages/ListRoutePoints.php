<?php

namespace App\Filament\Resources\RoutePoints\Pages;

use App\Filament\Resources\RoutePoints\RoutePointResource;
use Filament\Actions\CreateAction;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListRoutePoints extends ListRecords
{
    protected static string $resource = RoutePointResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('quickCreate')->label('Rychle přidat z telefonu')->url(route('route.quick.create'))->icon('heroicon-o-map-pin'),
            CreateAction::make()->label('Přidat bod trasy'),
        ];
    }
}
