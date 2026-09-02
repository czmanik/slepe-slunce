<?php

namespace App\Filament\Resources\TerminalPayments\Pages;

use App\Filament\Resources\TerminalPayments\TerminalPaymentResource;
use App\Services\ComgateTerminal;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Throwable;

class ListTerminalPayments extends ListRecords
{
    protected static string $resource = TerminalPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('createTerminalPayment')
                ->label('Vyžádat platbu na terminálu')
                ->icon('heroicon-o-credit-card')
                ->visible(fn (): bool => app(ComgateTerminal::class)->configured())
                ->form([
                    TextInput::make('amount')->label('Částka v Kč')->numeric()->required()->minValue(1),
                    TextInput::make('reason')->label('Důvod platby')->required()->maxLength(250),
                ])
                ->action(function (array $data): void {
                    try {
                        $payment = app(ComgateTerminal::class)->create(null, (int) round(((float) $data['amount']) * 100), $data['reason']);
                        Notification::make()->success()->title('Terminál byl vyzván k platbě')->body('Po zaplacení otevřete tento záznam a ověřte stav.')->send();
                    } catch (Throwable $exception) {
                        report($exception);
                        Notification::make()->danger()->title('Platbu na terminálu se nepodařilo zahájit')->body($exception->getMessage())->send();
                    }
                }),
        ];
    }
}
