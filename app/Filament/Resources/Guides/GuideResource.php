<?php

namespace App\Filament\Resources\Guides;

use App\Filament\Resources\Guides\Pages\CreateGuide;
use App\Filament\Resources\Guides\Pages\EditGuide;
use App\Filament\Resources\Guides\Pages\ListGuides;
use App\Filament\Resources\Posts\PostResource;
use App\Models\Post;

class GuideResource extends PostResource
{
    protected static ?string $navigationLabel = 'Návody';

    protected static ?string $slug = 'navody';

    protected static function contentCategory(): string
    {
        return Post::CATEGORY_TRAVEL;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGuides::route('/'),
            'create' => CreateGuide::route('/create'),
            'edit' => EditGuide::route('/{record}/edit'),
        ];
    }
}
