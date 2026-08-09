<?php

namespace App\Filament\Resources\RouteSegments\Pages;

use App\Filament\Resources\RouteSegments\RouteSegmentResource;
use App\Services\RouteGeometryService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Throwable;

class CreateRouteSegment extends CreateRecord
{
    protected static string $resource = RouteSegmentResource::class;

    protected function afterCreate(): void
    {
        try {
            app(RouteGeometryService::class)->refresh($this->record);
        } catch (Throwable $exception) {
            report($exception);
            Notification::make()->warning()->title('Úsek je uložený, geometrie je pouze orientační')->body($exception->getMessage())->send();
        }
    }
}
