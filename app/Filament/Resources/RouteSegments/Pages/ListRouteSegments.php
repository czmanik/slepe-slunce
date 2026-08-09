<?php

namespace App\Filament\Resources\RouteSegments\Pages;

use App\Filament\Resources\RouteSegments\RouteSegmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRouteSegments extends ListRecords
{
    protected static string $resource = RouteSegmentResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Přidat přesun')];
    }
}
