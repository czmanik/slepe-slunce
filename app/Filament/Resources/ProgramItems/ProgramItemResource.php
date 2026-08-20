<?php

namespace App\Filament\Resources\ProgramItems;

use App\Enums\ProgramItemKind;
use App\Filament\Resources\ProgramItems\Pages\CreateProgramItem;
use App\Filament\Resources\ProgramItems\Pages\EditProgramItem;
use App\Filament\Resources\ProgramItems\Pages\ListProgramItems;
use App\Models\ProgramItem;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DateTimePicker;
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

class ProgramItemResource extends Resource
{
    protected static ?string $model = ProgramItem::class;

    protected static ?string $modelLabel = 'položka programu';

    protected static ?string $pluralModelLabel = 'program';

    protected static string|UnitEnum|null $navigationGroup = 'Expedice';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([Section::make('Položka programu')->schema([
            Select::make('expedition_id')->label('Expedice')->relationship('expedition', 'name')->required()->searchable()->preload(),
            Select::make('kind')->label('Typ')->options(ProgramItemKind::options())->required()->default('activity'),
            TextInput::make('title')->label('Název')->required()->maxLength(180), Textarea::make('description')->label('Popis')->rows(4)->columnSpanFull(),
            DateTimePicker::make('starts_at')->label('Začátek')->seconds(false)->native(false), DateTimePicker::make('ends_at')->label('Konec')->seconds(false)->native(false)->after('starts_at'),
            TextInput::make('sort_order')->label('Pořadí')->integer()->default(0)->required(), Checkbox::make('is_public')->label('Veřejná položka')->default(true),
        ])->columns(2)]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('sort_order')->reorderable('sort_order')->columns([
            TextColumn::make('expedition.name')->label('Expedice'), TextColumn::make('sort_order')->label('Pořadí'), TextColumn::make('kind')->label('Typ')->badge()->formatStateUsing(fn ($state) => $state->label()), TextColumn::make('title')->label('Položka')->searchable(), TextColumn::make('starts_at')->label('Začátek')->dateTime('j. n. Y H:i'),
        ])->filters([SelectFilter::make('expedition_id')->relationship('expedition', 'name')])->recordActions([EditAction::make()])->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ListProgramItems::route('/'), 'create' => CreateProgramItem::route('/create'), 'edit' => EditProgramItem::route('/{record}/edit')];
    }
}
