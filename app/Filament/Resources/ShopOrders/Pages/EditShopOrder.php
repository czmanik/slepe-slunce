<?php

namespace App\Filament\Resources\ShopOrders\Pages;

use App\Filament\Resources\ShopOrders\ShopOrderResource;
use App\Services\ComgateGateway;
use Filament\Resources\Pages\EditRecord;

class EditShopOrder extends EditRecord
{
    protected static string $resource = ShopOrderResource::class;

    private bool $releaseStock = false;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->releaseStock = $data['status'] === 'cancelled' && $this->record->status !== 'cancelled';

        return $data;
    }

    protected function afterSave(): void
    {
        if ($this->releaseStock) {
            app(ComgateGateway::class)->releaseReservations($this->record);
        }
    }
}
