<?php

namespace App\Filament\Resources\ExpeditionRegistrations;

use App\Enums\PaymentStatus;
use App\Enums\RegistrationStatus;
use App\Filament\Resources\ExpeditionRegistrations\Pages\EditExpeditionRegistration;
use App\Filament\Resources\ExpeditionRegistrations\Pages\ListExpeditionRegistrations;
use App\Models\ExpeditionRegistration;
use Filament\Actions\EditAction;
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

class ExpeditionRegistrationResource extends Resource
{
    protected static ?string $model = ExpeditionRegistration::class;

    protected static ?string $modelLabel = 'přihláška';

    protected static ?string $pluralModelLabel = 'přihlášky';

    protected static string|UnitEnum|null $navigationGroup = 'Expedice';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Účastník')->schema([
                Select::make('expedition_id')->label('Expedice')->relationship('expedition', 'name')->disabled()->dehydrated(),
                TextInput::make('name')->label('Jméno')->required(), TextInput::make('email')->label('E-mail')->email()->required(),
                TextInput::make('phone')->label('Telefon'), TextInput::make('party_size')->label('Počet osob')->integer()->required(),
                Textarea::make('assistance_needs')->label('Potřeby asistence')->rows(4),
                Textarea::make('dietary_needs')->label('Stravovací omezení')->rows(3),
                Textarea::make('note')->label('Poznámka')->rows(4),
            ])->columns(2),
            Section::make('Schválení a platba')->schema([
                Select::make('status')->label('Stav přihlášky')->options(RegistrationStatus::options())->required(),
                Select::make('payment_status')->label('Platba')->options(PaymentStatus::options())->required(),
                TextInput::make('amount_due')->label('Částka k úhradě')->numeric(), TextInput::make('amount_paid')->label('Zaplaceno')->numeric(),
                TextInput::make('currency')->label('Měna')->maxLength(3), TextInput::make('discount_amount')->label('Sleva')->numeric(),
                Textarea::make('discount_note')->label('Důvod slevy')->rows(2),
                DateTimePicker::make('hold_expires_at')->label('Rezervace platí do')->seconds(false)->native(false),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('created_at', 'desc')->columns([
            TextColumn::make('expedition.name')->label('Expedice')->sortable(), TextColumn::make('name')->label('Zájemce')->searchable(),
            TextColumn::make('party_size')->label('Osob'), TextColumn::make('status')->label('Stav')->badge()->formatStateUsing(fn ($state) => $state->label()),
            TextColumn::make('payment_status')->label('Platba')->badge()->formatStateUsing(fn ($state) => $state->label()),
            TextColumn::make('hold_expires_at')->label('Rezervace do')->dateTime('j. n. Y H:i'),
        ])->filters([SelectFilter::make('expedition_id')->relationship('expedition', 'name'), SelectFilter::make('status')->options(RegistrationStatus::options())])->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ListExpeditionRegistrations::route('/'), 'edit' => EditExpeditionRegistration::route('/{record}/edit')];
    }
}
