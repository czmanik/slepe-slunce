<?php

namespace App\Filament\Resources\Expeditions\Pages;

use App\Jobs\TranslateContent;

use App\Filament\Resources\Expeditions\ExpeditionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateExpedition extends CreateRecord
{
    protected static string $resource = ExpeditionResource::class;
    protected function afterCreate(): void
    {
        TranslateContent::dispatch($this->record);
    }
}
