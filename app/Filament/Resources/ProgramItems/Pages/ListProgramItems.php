<?php

namespace App\Filament\Resources\ProgramItems\Pages;

use App\Filament\Resources\ProgramItems\ProgramItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProgramItems extends ListRecords
{
    protected static string $resource = ProgramItemResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
