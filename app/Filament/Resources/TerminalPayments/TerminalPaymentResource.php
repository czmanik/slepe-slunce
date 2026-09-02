<?php

namespace App\Filament\Resources\TerminalPayments;

use App\Filament\Resources\TerminalPayments\Pages\ListTerminalPayments;
use App\Models\TerminalPayment;
use App\Services\ComgateTerminal;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class TerminalPaymentResource extends Resource
{
    protected static ?string $model = TerminalPayment::class;

    protected static ?string $modelLabel = 'platba na terminálu';

    protected static ?string $pluralModelLabel = 'platby na terminálu';

    protected static string|UnitEnum|null $navigationGroup = 'Platby';

    protected static ?string $navigationLabel = 'Platba na terminálu';

    public static function table(Table $table): Table
    {
        return $table->defaultSort('created_at', 'desc')->columns([
            TextColumn::make('created_at')->label('Vytvořena')->dateTime('j. n. Y H:i'),
            TextColumn::make('reason')->label('Důvod')->searchable(),
            TextColumn::make('amount')->label('Částka')->formatStateUsing(fn ($state, TerminalPayment $record): string => number_format($state / 100, 2, ',', ' ').' '.$record->currency),
            TextColumn::make('status')->label('Stav Comgate')->badge(),
            TextColumn::make('registration.name')->label('Registrace')->placeholder('Samostatná platba'),
        ])->recordActions([
            Action::make('refresh')
                ->label('Ověřit stav')
                ->visible(fn (TerminalPayment $record): bool => $record->status === 'PENDING' && app(ComgateTerminal::class)->configured())
                ->action(function (TerminalPayment $record): void {
                    $status = app(ComgateTerminal::class)->refresh($record);
                    Notification::make()->success()->title('Stav platby: '.$status)->send();
                }),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListTerminalPayments::route('/')];
    }
}
