<?php
namespace App\Filament\Resources\MapPhotos\Pages;
use App\Filament\Resources\MapPhotos\MapPhotoResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
class EditMapPhoto extends EditRecord { protected static string $resource = MapPhotoResource::class; protected function getHeaderActions(): array { return [DeleteAction::make()]; } }
