<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Enums\PostStatus;
use App\Filament\Resources\Posts\PostResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePost extends CreateRecord
{
    protected static string $resource = PostResource::class;
    protected static bool $canCreateAnother = false;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        if (! auth()->user()->canPublish()) { $data['status'] = PostStatus::Draft; $data['published_at'] = null; }
        if (in_array($data['status'], [PostStatus::Published->value, PostStatus::Scheduled->value], true) && empty($data['published_at'])) { $data['published_at'] = now(); }
        return $data;
    }
}
