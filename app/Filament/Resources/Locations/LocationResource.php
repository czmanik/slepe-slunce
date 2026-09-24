<?php

namespace App\Filament\Resources\Locations;

use App\Filament\Resources\Locations\Pages\CreateLocation;
use App\Filament\Resources\Locations\Pages\EditLocation;
use App\Filament\Resources\Locations\Pages\ListLocations;
use App\Models\Location;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class LocationResource extends Resource
{
    protected static ?string $model = Location::class;

    protected static ?string $modelLabel = 'místo';

    protected static ?string $pluralModelLabel = 'místa';

    protected static string|UnitEnum|null $navigationGroup = 'Expedice';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([Section::make('Znovu použitelné místo')->schema([
            TextInput::make('name')->label('Název')->required()->maxLength(180), TextInput::make('address')->label('Adresa')->maxLength(300), TextInput::make('country_code')->label('Kód země')->maxLength(2),
            TextInput::make('latitude')->label('Zeměpisná šířka')->numeric()->required()->minValue(-90)->maxValue(90), TextInput::make('longitude')->label('Zeměpisná délka')->numeric()->required()->minValue(-180)->maxValue(180),
        ])->columns(2)]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('name')->label('Místo')->searchable(), TextColumn::make('address')->label('Adresa')->searchable(), TextColumn::make('country_code')->label('Země')])->recordActions([EditAction::make()])->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ListLocations::route('/'), 'create' => CreateLocation::route('/create'), 'edit' => EditLocation::route('/{record}/edit')];
    }
}
