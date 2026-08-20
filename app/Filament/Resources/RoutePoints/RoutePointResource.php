<?php

namespace App\Filament\Resources\RoutePoints;

use App\Enums\RoutePointStatus;
use App\Filament\Resources\RoutePoints\Pages\CreateRoutePoint;
use App\Filament\Resources\RoutePoints\Pages\EditRoutePoint;
use App\Filament\Resources\RoutePoints\Pages\ListRoutePoints;
use App\Models\RoutePoint;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class RoutePointResource extends Resource
{
    protected static ?string $model = RoutePoint::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'bod trasy';

    protected static ?string $pluralModelLabel = 'body trasy';

    protected static ?string $navigationLabel = 'Trasa';

    protected static string|UnitEnum|null $navigationGroup = 'Expedice';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Zastávka')->schema([
                Select::make('expedition_id')->label('Expedice')->relationship('expedition', 'name')->required()->searchable()->preload(),
                TextInput::make('name')->label('Název')->required()->maxLength(160),
                Textarea::make('description')->label('Krátký popis')->rows(3)->maxLength(700)->columnSpanFull(),
                Select::make('post_id')->label('Propojený článek')->relationship('post', 'title')->searchable()->preload(),
                DateTimePicker::make('occurred_at')->label('Datum a čas')->seconds(false)->native(false),
            ])->columns(2),

            Section::make('Poloha a pořadí')->description('Souřadnice zkopírujte z mapy. Na telefonu stačí dlouze podržet místo v Mapy.cz nebo Google Maps.')->schema([
                Select::make('location_id')->label('Uložené místo')->relationship('location', 'name')->searchable()->preload()->helperText('Volitelné. Při uložení převezme bod souřadnice z katalogu míst.'),
                TextInput::make('latitude')->label('Zeměpisná šířka')->numeric()->required()->minValue(-90)->maxValue(90)->step('0.0000001')->placeholder('41.3873974'),
                TextInput::make('longitude')->label('Zeměpisná délka')->numeric()->required()->minValue(-180)->maxValue(180)->step('0.0000001')->placeholder('2.1685680'),
                TextInput::make('route_order')->label('Pořadí na trase')->numeric()->integer()->minValue(0)->required()->default(0),
                Select::make('status')->label('Stav')->options(RoutePointStatus::options())->required()->default(RoutePointStatus::Planned->value),
                Checkbox::make('is_goal')->label('Důležitý cíl expedice'),
            ])->columns(2),

            Section::make('Hlavní fotografie')->description('Pokud nahrajete fotografii, doplňte také alternativní text.')->schema([
                FileUpload::make('cover_image')->label('Fotografie')->image()->disk('public')->directory('route/covers')->visibility('public')->maxSize(12288),
                Textarea::make('cover_alt')->label('Alternativní text')->requiredWith('cover_image')->rows(2)->maxLength(300),
            ])->columns(2),

            Section::make('Další fotografie')->schema([
                Repeater::make('gallery')->label('Galerie')->reorderable()->collapsible()->schema([
                    FileUpload::make('path')->label('Soubor')->image()->disk('public')->directory('route/gallery')->visibility('public')->maxSize(12288)->required(),
                    Textarea::make('alt')->label('Alternativní text')->required()->rows(2)->maxLength(300),
                    TextInput::make('caption')->label('Popisek')->maxLength(300),
                ])->columns(2)->defaultItems(0)->columnSpanFull(),
            ])->collapsed(),

            Section::make('YouTube videa')->schema([
                Repeater::make('videos')->label('Videa')->reorderable()->collapsible()->schema([
                    TextInput::make('url')->label('YouTube odkaz')->url()->regex('/^https?:\\/\\/(?:www\\.)?(?:youtube(?:-nocookie)?\\.com|youtu\\.be)\\//i')->required(),
                    TextInput::make('title')->label('Název videa')->required()->maxLength(180),
                    Textarea::make('description')->label('Popis obsahu')->rows(2)->maxLength(500),
                ])->columns(2)->defaultItems(0)->columnSpanFull(),
            ])->collapsed(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('route_order')->reorderable('route_order')->columns([
            TextColumn::make('route_order')->label('Pořadí')->sortable(),
            TextColumn::make('name')->label('Místo')->searchable()->sortable(),
            TextColumn::make('expedition.name')->label('Expedice')->sortable(),
            TextColumn::make('status')->label('Stav')->badge()->formatStateUsing(fn (RoutePointStatus $state): string => $state->label())
                ->color(fn (RoutePointStatus $state): string => match ($state) {
                    RoutePointStatus::Visited => 'success', RoutePointStatus::Current => 'warning', default => 'gray'
                }),
            IconColumn::make('is_goal')->label('Cíl')->boolean(),
            TextColumn::make('occurred_at')->label('Datum')->dateTime('j. n. Y H:i')->sortable(),
        ])->filters([
            SelectFilter::make('status')->label('Stav')->options(RoutePointStatus::options()),
            SelectFilter::make('expedition_id')->label('Expedice')->relationship('expedition', 'name'),
        ])->recordActions([EditAction::make()])->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ListRoutePoints::route('/'), 'create' => CreateRoutePoint::route('/create'), 'edit' => EditRoutePoint::route('/{record}/edit')];
    }
}
