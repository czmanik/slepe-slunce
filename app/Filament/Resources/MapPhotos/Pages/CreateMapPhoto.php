<?php
namespace App\Filament\Resources\MapPhotos\Pages;
use App\Filament\Resources\MapPhotos\MapPhotoResource;
use Filament\Resources\Pages\CreateRecord;
class CreateMapPhoto extends CreateRecord { protected static string $resource = MapPhotoResource::class; protected function mutateFormDataBeforeCreate(array $data): array { $data['user_id'] = auth()->id(); return $data; } }
