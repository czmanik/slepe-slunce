<?php

namespace App\Filament\Resources\Expeditions\Pages;

use App\Jobs\TranslateContent;

use App\Filament\Resources\Expeditions\ExpeditionResource;
use Filament\Actions\DeleteAction;
use App\Filament\Support\ContentTranslationActions;
use Filament\Resources\Pages\EditRecord;

class EditExpedition extends EditRecord
{
    protected static string $resource = ExpeditionResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make(), ...ContentTranslationActions::for($this->record)];
    }
    protected function afterSave(): void
    {
        TranslateContent::dispatch($this->record);
    }
}
