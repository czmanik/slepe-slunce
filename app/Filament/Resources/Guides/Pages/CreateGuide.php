<?php

namespace App\Filament\Resources\Guides\Pages;

use App\Filament\Resources\Guides\GuideResource;
use App\Models\Post;
use Filament\Resources\Pages\CreateRecord;

class CreateGuide extends CreateRecord
{
    protected static string $resource = GuideResource::class;

    protected static bool $canCreateAnother = false;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        $data['category'] = Post::CATEGORY_TRAVEL;

        if (! auth()->user()->canPublish()) {
            $data['status'] = \App\Enums\PostStatus::Draft;
            $data['published_at'] = null;
        }

        if (in_array($data['status'], [\App\Enums\PostStatus::Published->value, \App\Enums\PostStatus::Scheduled->value], true) && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        return $data;
    }
}
