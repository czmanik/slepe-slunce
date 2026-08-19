<?php

namespace App\Filament\Resources\MapPhotos;

use App\Filament\Resources\MapPhotos\Pages\CreateMapPhoto;
use App\Filament\Resources\MapPhotos\Pages\EditMapPhoto;
use App\Filament\Resources\MapPhotos\Pages\ListMapPhotos;
use App\Models\MapPhoto;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class MapPhotoResource extends Resource
{
    protected static ?string $model = MapPhoto::class;

    protected static ?string $modelLabel = 'fotografie na mapě';

    protected static ?string $pluralModelLabel = 'fotografie na mapě';

    protected static ?string $navigationLabel = 'Fotografie na mapě';

    protected static string|UnitEnum|null $navigationGroup = 'Expedice';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('expedition_id')->label('Expedice')->relationship('expedition', 'name')->required()->searchable()->preload(),
            FileUpload::make('image')->label('Fotografie')->image()->disk('public')->directory('map/photos')->required()->maxSize(15360),
            Textarea::make('alt')->label('Alternativní text')->required()->maxLength(300),
            Textarea::make('caption')->label('Popisek')->maxLength(500),
            TextInput::make('latitude')->label('Zeměpisná šířka')->numeric()->required()->minValue(-90)->maxValue(90),
            TextInput::make('longitude')->label('Zeměpisná délka')->numeric()->required()->minValue(-180)->maxValue(180),
            DateTimePicker::make('taken_at')->label('Pořízeno')->seconds(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('taken_at', 'desc')->columns([
            ImageColumn::make('image')->label('Náhled')->disk('public'),
            TextColumn::make('caption')->label('Popisek')->limit(60)->searchable(),
            TextColumn::make('expedition.name')->label('Expedice'),
            TextColumn::make('user.name')->label('Přidal'),
            TextColumn::make('taken_at')->label('Pořízeno')->dateTime('j. n. Y H:i'),
        ])->recordActions([EditAction::make()])->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ListMapPhotos::route('/'), 'create' => CreateMapPhoto::route('/create'), 'edit' => EditMapPhoto::route('/{record}/edit')];
    }
}
