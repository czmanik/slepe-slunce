<?php

namespace App\Filament\Resources\ShopOrders;

use App\Filament\Resources\ShopOrders\Pages\EditShopOrder;
use App\Filament\Resources\ShopOrders\Pages\ListShopOrders;
use App\Models\ShopOrder;
use Filament\Actions\EditAction;
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

class ShopOrderResource extends Resource
{
    protected static ?string $model = ShopOrder::class;

    protected static ?string $modelLabel = 'objednávka';

    protected static ?string $pluralModelLabel = 'objednávky';

    protected static string|UnitEnum|null $navigationGroup = 'Obchod';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([Section::make('Objednávka')->schema([TextInput::make('number')->label('Číslo')->disabled(), TextInput::make('customer_name')->label('Zákazník')->disabled(), TextInput::make('email')->label('E-mail')->disabled(), Select::make('status')->label('Stav')->options(['new' => 'Nová', 'processing' => 'Zpracovává se', 'ready' => 'Připraveno k odběru', 'completed' => 'Dokončena', 'cancelled' => 'Zrušena'])->required(), Select::make('payment_status')->label('Platba')->options(['unpaid' => 'Nezaplaceno', 'pending' => 'Čeká se', 'paid' => 'Zaplaceno', 'cancelled' => 'Zrušeno', 'refunded' => 'Vráceno'])->required(), TextInput::make('invoice_number')->label('Číslo faktury'), Textarea::make('note')->label('Poznámka zákazníka')->disabled()->columnSpanFull()])->columns(2)]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('created_at', 'desc')->columns([TextColumn::make('number')->label('Číslo')->searchable(), TextColumn::make('customer_name')->label('Zákazník')->searchable(), TextColumn::make('grand_total')->label('Celkem')->formatStateUsing(fn ($state, ShopOrder $record) => number_format($state / 100, 2, ',', ' ').' '.$record->currency), TextColumn::make('status')->label('Stav')->badge(), TextColumn::make('payment_status')->label('Platba')->badge(), TextColumn::make('created_at')->label('Vytvořena')->dateTime('j. n. Y H:i')])->filters([SelectFilter::make('status')->options(['new' => 'Nová', 'processing' => 'Zpracovává se', 'ready' => 'Připraveno', 'completed' => 'Dokončena', 'cancelled' => 'Zrušena'])])->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ListShopOrders::route('/'), 'edit' => EditShopOrder::route('/{record}/edit')];
    }
}
