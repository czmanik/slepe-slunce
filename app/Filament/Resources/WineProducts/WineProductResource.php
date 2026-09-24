<?php

namespace App\Filament\Resources\WineProducts;

use App\Filament\Resources\WineProducts\Pages\CreateWineProduct;
use App\Filament\Resources\WineProducts\Pages\EditWineProduct;
use App\Filament\Resources\WineProducts\Pages\ListWineProducts;
use App\Models\WineProduct;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class WineProductResource extends Resource
{
    protected static ?string $model = WineProduct::class;

    protected static ?string $modelLabel = 'víno';

    protected static ?string $pluralModelLabel = 'vína';

    protected static string|UnitEnum|null $navigationGroup = 'Obchod';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Víno')->schema([TextInput::make('name')->label('Název')->required(), TextInput::make('slug')->label('Adresa')->required()->unique(ignoreRecord: true), TextInput::make('winery')->label('Vinařství'), Textarea::make('description')->label('Popis')->rows(5)->columnSpanFull(), Checkbox::make('is_active')->label('Veřejně v prodeji'), Checkbox::make('is_archival')->label('Archivní víno')->default(true), FileUpload::make('image')->label('Fotografie')->image()->disk('public')->directory('shop/wines'), Textarea::make('image_alt')->label('Alternativní text')->requiredWith('image')])->columns(2),
            Section::make('Ročníky a varianty')->schema([Repeater::make('variants')->relationship()->schema([TextInput::make('sku')->label('SKU')->required(), TextInput::make('vintage')->label('Ročník')->integer(), TextInput::make('bottle_size')->label('Objem')->default('0,75 l')->required(), TextInput::make('quality')->label('Jakost'), TextInput::make('price_czk')->label('Cena CZK v haléřích')->integer()->required(), TextInput::make('price_eur')->label('Cena EUR v centech')->integer(), TextInput::make('vat_rate')->label('DPH %')->integer()->default(21)->required(), TextInput::make('stock_quantity')->label('Sklad')->integer()->default(0)->required(), Checkbox::make('is_active')->label('V prodeji')])->columns(3)->columnSpanFull()]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('name')->label('Víno')->searchable(), TextColumn::make('winery')->label('Vinařství')->searchable(), TextColumn::make('variants_count')->counts('variants')->label('Variant'), TextColumn::make('variants_stock_quantity')->sum('variants', 'stock_quantity')->label('Lahví'), IconColumn::make('is_active')->label('V prodeji')->boolean()])->recordActions([EditAction::make()])->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ListWineProducts::route('/'), 'create' => CreateWineProduct::route('/create'), 'edit' => EditWineProduct::route('/{record}/edit')];
    }
}
