<?php

namespace App\Filament\Resources\RouteSegments\Pages;

use App\Filament\Resources\RouteSegments\RouteSegmentResource;
use App\Services\RouteGeometryService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Throwable;

class EditRouteSegment extends EditRecord
{
    protected static string $resource = RouteSegmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('recalculate')->label('Přepočítat trasu')->icon('heroicon-o-arrow-path')->requiresConfirmation()
                ->modalDescription('Přepočítá geometrii, vzdálenost a orientační dobu cesty podle aktuálních bodů a průjezdních míst.')
                ->action(fn () => $this->refreshGeometry(overwriteMetrics: true)),
            DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $geometryChanged = $this->record->wasChanged(['from_point_id', 'to_point_id', 'transport_mode', 'geometry_mode', 'waypoints']);
        $this->refreshGeometry(
            notifySuccess: false,
            overwriteDistance: $geometryChanged && ! $this->record->wasChanged('distance_km'),
            overwriteDuration: $geometryChanged && ! $this->record->wasChanged('duration_minutes'),
        );
    }

    private function refreshGeometry(bool $notifySuccess = true, bool $overwriteMetrics = false, bool $overwriteDistance = false, bool $overwriteDuration = false): void
    {
        try {
            app(RouteGeometryService::class)->refresh(
                $this->record,
                overwriteDistance: $overwriteMetrics || $overwriteDistance,
                overwriteDuration: $overwriteMetrics || $overwriteDuration,
            );
            if ($notifySuccess) {
                Notification::make()->success()->title('Trasa byla přepočítána')->send();
            }
        } catch (Throwable $exception) {
            report($exception);
            Notification::make()->warning()->title('Geometrii se nepodařilo plně přepočítat')->body($exception->getMessage())->send();
        }
    }
}
