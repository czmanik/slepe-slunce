<?php

namespace App\Filament\Resources\ExpeditionRegistrations\Pages;

use App\Filament\Resources\ExpeditionRegistrations\ExpeditionRegistrationResource;
use App\Services\ComgateTerminal;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Throwable;

class EditExpeditionRegistration extends EditRecord
{
    protected static string $resource = ExpeditionRegistrationResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['reviewed_by'] = auth()->id();
        $data['reviewed_at'] = now();
        if ($data['status'] === 'approved' && empty($data['hold_expires_at'])) {
            $data['hold_expires_at'] = now()->addHours($this->record->expedition->reservation_hold_hours);
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('terminalPayment')
                ->label('Vyžádat platbu kartou na terminálu')
                ->icon('heroicon-o-credit-card')
                ->visible(fn (): bool => app(ComgateTerminal::class)->configured() && $this->outstandingAmount() > 0 && ! $this->record->terminalPayments()->where('status', 'PENDING')->exists())
                ->form([
                    TextInput::make('amount')->label('Částka v Kč')->numeric()->required()->minValue(1)->maxValue(fn (): float => $this->outstandingAmount())->default(fn (): float => $this->outstandingAmount()),
                ])
                ->action(function (array $data): void {
                    try {
                        $payment = app(ComgateTerminal::class)->create($this->record, (int) round(((float) $data['amount']) * 100));
                        Notification::make()->success()->title('Terminál byl vyzván k platbě')->body('Částka: '.number_format($payment->amount / 100, 2, ',', ' ').' '.$payment->currency.'. Po zaplacení stiskněte „Ověřit stav terminálu“.')->send();
                    } catch (Throwable $exception) {
                        report($exception);
                        Notification::make()->danger()->title('Platbu na terminálu se nepodařilo zahájit')->body($exception->getMessage())->send();
                    }
                }),
            Action::make('refreshTerminalPayment')
                ->label('Ověřit stav terminálu')
                ->icon('heroicon-o-arrow-path')
                ->visible(fn (): bool => app(ComgateTerminal::class)->configured() && $this->record->terminalPayments()->where('status', 'PENDING')->exists())
                ->action(function (): void {
                    try {
                        $payment = $this->record->terminalPayments()->where('status', 'PENDING')->latest()->firstOrFail();
                        $status = app(ComgateTerminal::class)->refresh($payment);
                        $this->record->refresh();
                        Notification::make()->{$status === 'PAID' ? 'success' : 'warning'}()->title($status === 'PAID' ? 'Platba kartou je potvrzená' : 'Terminál zatím platbu nepotvrdil')->body('Stav Comgate: '.$status)->send();
                    } catch (Throwable $exception) {
                        report($exception);
                        Notification::make()->danger()->title('Stav terminálu se nepodařilo ověřit')->body($exception->getMessage())->send();
                    }
                }),
        ];
    }

    private function outstandingAmount(): float
    {
        return max(0, round((float) $this->record->amount_due - (float) $this->record->discount_amount - (float) $this->record->amount_paid, 2));
    }
}
