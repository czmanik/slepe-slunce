<?php

namespace App\Filament\Resources\Authors;

use App\Filament\Resources\Authors\Pages\CreateAuthor;
use App\Filament\Resources\Authors\Pages\EditAuthor;
use App\Filament\Resources\Authors\Pages\ListAuthors;
use App\Models\Author;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AuthorResource extends Resource
{
    protected static ?string $model = Author::class;
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $modelLabel = 'autor';
    protected static ?string $pluralModelLabel = 'autoři';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Jméno')->required()->maxLength(120),
            Textarea::make('bio')->label('Krátké představení')->rows(5)->maxLength(1000)->columnSpanFull(),
            FileUpload::make('photo')->label('Fotografie člena')->image()->disk('public')->directory('members')->visibility('public')->maxSize(12288),
            Textarea::make('photo_alt')->label('Alternativní text fotografie')->requiredWith('photo')->rows(2)->maxLength(300),
            Toggle::make('is_expedition_member')->label('Člen expedice')->default(true),
            TextInput::make('sort_order')->label('Pořadí')->numeric()->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->label('Jméno')->searchable()->sortable(),
            IconColumn::make('is_expedition_member')->label('Expedice')->boolean(),
            TextColumn::make('posts_count')->label('Příspěvky')->counts('posts'),
        ])->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ListAuthors::route('/'), 'create' => CreateAuthor::route('/create'), 'edit' => EditAuthor::route('/{record}/edit')];
    }
}
