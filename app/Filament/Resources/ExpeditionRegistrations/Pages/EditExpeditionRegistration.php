<?php

namespace App\Filament\Resources\ExpeditionRegistrations\Pages;

use App\Filament\Resources\ExpeditionRegistrations\ExpeditionRegistrationResource;
use Filament\Resources\Pages\EditRecord;

class EditExpeditionRegistration extends EditRecord
{
    protected static string $resource = ExpeditionRegistrationResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['reviewed_by'] = auth()->id();
        $data['reviewed_at'] = now();
        if ($data['status'] === 'approved' && empty($data['hold_expires_at'])) {
            $data['hold_expires_at'] = now()->addHours($this->record->expedition->reservation_hold_hours);
        }

        return $data;
    }
}
