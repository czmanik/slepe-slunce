<?php

namespace App\Filament\Resources\Guides\Pages;

use App\Filament\Resources\Guides\GuideResource;
use App\Models\Post;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditGuide extends EditRecord
{
    protected static string $resource = GuideResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')->label('Náhled')->url(fn (): string => route('guides.show', $this->record))->openUrlInNewTab(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
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
