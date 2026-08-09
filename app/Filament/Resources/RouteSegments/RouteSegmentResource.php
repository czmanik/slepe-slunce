<?php

namespace App\Filament\Resources\RouteSegments;

use App\Enums\RouteSegmentStatus;
use App\Enums\TransportMode;
use App\Filament\Resources\RouteSegments\Pages\CreateRouteSegment;
use App\Filament\Resources\RouteSegments\Pages\EditRouteSegment;
use App\Filament\Resources\RouteSegments\Pages\ListRouteSegments;
use App\Models\RouteSegment;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class RouteSegmentResource extends Resource
{
    protected static ?string $model = RouteSegment::class;
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $modelLabel = 'úsek cesty';
    protected static ?string $pluralModelLabel = 'úseky cesty';
    protected static ?string $navigationLabel = 'Přesuny';
    protected static string | UnitEnum | null $navigationGroup = 'Expedice';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Odkud a kam')->description('Úsek propojuje dva existující body trasy. Body musí být nejprve založené v sekci Trasa.')->schema([
                Select::make('from_point_id')->label('Výchozí bod')->relationship('fromPoint', 'name')->searchable()->preload()->required(),
                Select::make('to_point_id')->label('Cílový bod')->relationship('toPoint', 'name')->searchable()->preload()->required()->different('from_point_id'),
                TextInput::make('name')->label('Vlastní název úseku')->placeholder('Let Praha → Barcelona')->maxLength(180)->columnSpanFull(),
                Textarea::make('description')->label('Popis přesunu')->rows(3)->maxLength(1000)->columnSpanFull(),
                Select::make('post_id')->label('Propojený článek')->relationship('post', 'title')->searchable()->preload(),
                TextInput::make('sort_order')->label('Pořadí na trase')->numeric()->integer()->minValue(0)->required()->default(0),
            ])->columns(2),

            Section::make('Doprava a stav')->schema([
                Select::make('transport_mode')->label('Dopravní prostředek')->options(TransportMode::options())->required()->native(false),
                Select::make('status')->label('Stav')->options(RouteSegmentStatus::options())->required()->default(RouteSegmentStatus::Planned->value)->native(false),
                TextInput::make('provider')->label('Dopravce')->placeholder('Vueling, Renfe…')->maxLength(160),
                TextInput::make('reference')->label('Číslo letu / spoje')->maxLength(160),
                TextInput::make('distance_km')->label('Vzdálenost v km')->numeric()->minValue(0)->step('0.1')->helperText('Nechte prázdné pro automatický výpočet.'),
                TextInput::make('duration_minutes')->label('Délka v minutách')->numeric()->integer()->minValue(0)->helperText('Nechte prázdné pro automatický výpočet silniční trasy.'),
            ])->columns(2),

            Section::make('Plánované časy')->schema([
                DateTimePicker::make('scheduled_departure_at')->label('Plánovaný odjezd / odlet')->seconds(false)->native(false),
                DateTimePicker::make('scheduled_arrival_at')->label('Plánovaný příjezd / přílet')->seconds(false)->native(false)->afterOrEqual('scheduled_departure_at'),
            ])->columns(2),

            Section::make('Skutečné časy')->description('Po uskutečnění přesunu doplňte skutečné časy. Časová osa jim dá přednost před plánem.')->schema([
                DateTimePicker::make('departed_at')->label('Skutečný odjezd / odlet')->seconds(false)->native(false),
                DateTimePicker::make('arrived_at')->label('Skutečný příjezd / přílet')->seconds(false)->native(false)->afterOrEqual('departed_at'),
            ])->columns(2)->collapsed(),

            Section::make('Vykreslení na mapě')->description('Auto a autobus lze vést po silnici. Letadlo se automaticky zobrazí jako oblouk. U vlaku, lodi a dalších přesunů použijte průjezdní body, pokud přímá spojnice nestačí.')->schema([
                Select::make('geometry_mode')->label('Způsob vykreslení')->options([
                    'automatic' => 'Automaticky podle dopravy',
                    'direct' => 'Přímá spojnice přes průjezdní body',
                ])->required()->default('automatic')->native(false),
                Repeater::make('waypoints')->label('Průjezdní body')->helperText('Nepovinné. Zadejte je v pořadí cesty mezi výchozím a cílovým bodem.')->reorderable()->collapsible()->schema([
                    TextInput::make('name')->label('Název')->maxLength(120),
                    TextInput::make('latitude')->label('Zeměpisná šířka')->numeric()->required()->minValue(-90)->maxValue(90)->step('0.0000001'),
                    TextInput::make('longitude')->label('Zeměpisná délka')->numeric()->required()->minValue(-180)->maxValue(180)->step('0.0000001'),
                ])->columns(3)->columnSpanFull(),
            ])->columns(2),

            Section::make('Hlavní fotografie')->description('Fotografie a média se mohou vztahovat přímo k přesunu, nejen k zastávce.')->schema([
                FileUpload::make('cover_image')->label('Fotografie')->image()->disk('public')->directory('route/segments/covers')->visibility('public')->maxSize(12288),
                Textarea::make('cover_alt')->label('Alternativní text')->requiredWith('cover_image')->rows(2)->maxLength(300),
            ])->columns(2)->collapsed(),

            Section::make('Další fotografie')->schema([
                Repeater::make('gallery')->label('Galerie')->reorderable()->collapsible()->schema([
                    FileUpload::make('path')->label('Soubor')->image()->disk('public')->directory('route/segments/gallery')->visibility('public')->maxSize(12288)->required(),
                    Textarea::make('alt')->label('Alternativní text')->required()->rows(2)->maxLength(300),
                    TextInput::make('caption')->label('Popisek')->maxLength(300),
                ])->columns(2)->columnSpanFull(),
            ])->collapsed(),

            Section::make('YouTube videa')->schema([
                Repeater::make('videos')->label('Videa')->reorderable()->collapsible()->schema([
                    TextInput::make('url')->label('YouTube odkaz')->url()->regex('/^https?:\/\/(?:www\.)?(?:youtube(?:-nocookie)?\.com|youtu\.be)\//i')->required(),
                    TextInput::make('title')->label('Název videa')->required()->maxLength(180),
                    Textarea::make('description')->label('Popis obsahu')->rows(2)->maxLength(500),
                ])->columns(2)->columnSpanFull(),
            ])->collapsed(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('sort_order')->reorderable('sort_order')->columns([
            TextColumn::make('sort_order')->label('Pořadí')->sortable(),
            TextColumn::make('fromPoint.name')->label('Odkud')->searchable(),
            TextColumn::make('toPoint.name')->label('Kam')->searchable(),
            TextColumn::make('transport_mode')->label('Doprava')->badge()->formatStateUsing(fn (TransportMode $state): string => $state->label()),
            TextColumn::make('status')->label('Stav')->badge()->formatStateUsing(fn (RouteSegmentStatus $state): string => $state->label())
                ->color(fn (RouteSegmentStatus $state): string => match ($state) { RouteSegmentStatus::Completed => 'success', RouteSegmentStatus::InProgress => 'warning', default => 'gray' }),
            TextColumn::make('scheduled_departure_at')->label('Odjezd')->dateTime('j. n. Y H:i')->sortable(),
        ])->filters([
            SelectFilter::make('transport_mode')->label('Doprava')->options(TransportMode::options()),
            SelectFilter::make('status')->label('Stav')->options(RouteSegmentStatus::options()),
        ])->recordActions([EditAction::make()])->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRouteSegments::route('/'),
            'create' => CreateRouteSegment::route('/create'),
            'edit' => EditRouteSegment::route('/{record}/edit'),
        ];
    }
}
