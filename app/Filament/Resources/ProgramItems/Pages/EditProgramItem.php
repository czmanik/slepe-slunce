<?php

namespace App\Filament\Resources\ProgramItems\Pages;

use App\Filament\Resources\ProgramItems\ProgramItemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProgramItem extends EditRecord
{
    protected static string $resource = ProgramItemResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
