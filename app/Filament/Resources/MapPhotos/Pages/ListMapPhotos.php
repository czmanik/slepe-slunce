<?php
namespace App\Filament\Resources\MapPhotos\Pages;
use App\Filament\Resources\MapPhotos\MapPhotoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
class ListMapPhotos extends ListRecords { protected static string $resource = MapPhotoResource::class; protected function getHeaderActions(): array { return [CreateAction::make()]; } }
