<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Enums\PostStatus;
use App\Filament\Resources\Posts\PostResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPost extends EditRecord
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [Action::make('preview')->label('Náhled')->url(fn (): string => route('posts.preview', $this->record))->openUrlInNewTab(), DeleteAction::make()];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! auth()->user()->canPublish()) { $data['status'] = PostStatus::Draft; $data['published_at'] = null; }
        if (in_array($data['status'], [PostStatus::Published->value, PostStatus::Scheduled->value], true) && empty($data['published_at'])) { $data['published_at'] = now(); }
        return $data;
    }
}
