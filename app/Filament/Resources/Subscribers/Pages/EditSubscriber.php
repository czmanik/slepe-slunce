<?php

namespace App\Filament\Resources\Subscribers\Pages;

use App\Filament\Resources\Subscribers\SubscriberResource;
use Filament\Resources\Pages\EditRecord;

class EditSubscriber extends EditRecord
{
    protected static string $resource = SubscriberResource::class;
}
