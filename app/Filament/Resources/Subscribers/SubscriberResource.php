<?php

namespace App\Filament\Resources\Subscribers;

use App\Filament\Resources\Subscribers\Pages\EditSubscriber;
use App\Filament\Resources\Subscribers\Pages\ListSubscribers;
use App\Models\Subscriber;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SubscriberResource extends Resource
{
    protected static ?string $model = Subscriber::class;

    protected static ?string $modelLabel = 'odběratel';

    protected static ?string $pluralModelLabel = 'odběratelé';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([Section::make('Odběr')->schema([TextInput::make('email')->label('E-mail')->email()->required(), TextInput::make('name')->label('Jméno'), Select::make('status')->label('Stav')->options(['pending' => 'Čeká na potvrzení', 'active' => 'Aktivní', 'unsubscribed' => 'Odhlášen'])->required(), Checkbox::make('project_news')->label('Život projektu'), Checkbox::make('new_expeditions')->label('Nové expedice'), Checkbox::make('shop_news')->label('Obchod'), Select::make('expeditions')->label('Vybrané expedice')->relationship('expeditions', 'name')->multiple()->preload()])->columns(2)]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('created_at', 'desc')->columns([TextColumn::make('email')->label('E-mail')->searchable(), TextColumn::make('status')->label('Stav')->badge(), IconColumn::make('project_news')->label('Projekt')->boolean(), IconColumn::make('new_expeditions')->label('Expedice')->boolean(), IconColumn::make('shop_news')->label('Obchod')->boolean(), TextColumn::make('confirmed_at')->label('Potvrzeno')->dateTime('j. n. Y H:i')])->filters([SelectFilter::make('status')->options(['pending' => 'Čeká', 'active' => 'Aktivní', 'unsubscribed' => 'Odhlášen'])])->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ListSubscribers::route('/'), 'edit' => EditSubscriber::route('/{record}/edit')];
    }
}
