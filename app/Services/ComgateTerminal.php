<?php

namespace App\Services;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\RegistrationStatus;
use App\Models\ExpeditionRegistration;
use App\Models\TerminalPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ComgateTerminal
{
    public function configured(): bool
    {
        return config('shop.terminal.enabled')
            && filled(config('shop.terminal.login'))
            && filled(config('shop.terminal.secret'));
    }

    public function create(?ExpeditionRegistration $registration, int $amount, ?string $reason = null): TerminalPayment
    {
        if (! $this->configured()) {
            throw new RuntimeException('Terminál Comgate není nakonfigurován. Doplňte přístupové údaje CloudPOS do .env.');
        }
        if ($amount < 100) {
            throw new RuntimeException('Minimální platba na terminálu je 1 Kč.');
        }

        $reference = ($registration ? 'ER'.$registration->id : 'TP').'-'.now()->format('YmdHis');
        $currency = strtoupper($registration?->currency ?: 'CZK');
        $response = $this->client()->post($this->url('/terminalPayment.json'), [
            'price' => $amount,
            'curr' => $currency,
            'refId' => $reference,
        ]);
        $response->throw();
        $payload = $response->json();
        if ((int) data_get($payload, 'code') !== 0 || ! filled(data_get($payload, 'transId'))) {
            throw new RuntimeException('Comgate terminál odmítl platbu: '.(data_get($payload, 'message') ?: 'neznámá chyba'));
        }

        return TerminalPayment::query()->create([
            'expedition_registration_id' => $registration?->id, 'created_by' => auth()->id(), 'transaction_id' => data_get($payload, 'transId'), 'reference' => $reference, 'reason' => $reason,
            'status' => 'PENDING', 'amount' => $amount, 'currency' => $currency,
            'provider_payload' => $payload, 'checked_at' => now(),
        ]);
    }

    public function refresh(TerminalPayment $payment): string
    {
        if (! $this->configured()) {
            throw new RuntimeException('Terminál Comgate není nakonfigurován.');
        }

        $response = $this->client()->get($this->url('/terminalPayment/transId/'.rawurlencode($payment->transaction_id).'.json'));
        $response->throw();
        $payload = $response->json();
        if ((int) data_get($payload, 'code') !== 0) {
            throw new RuntimeException('Stav platby na terminálu se nepodařilo ověřit: '.(data_get($payload, 'message') ?: 'neznámá chyba'));
        }
        if (data_get($payload, 'transId') !== $payment->transaction_id || (int) data_get($payload, 'price') !== $payment->amount || data_get($payload, 'curr') !== $payment->currency) {
            throw new RuntimeException('Údaje vrácené terminálem neodpovídají zadané platbě.');
        }

        $status = strtoupper((string) data_get($payload, 'status', 'PENDING'));
        DB::transaction(function () use ($payment, $payload, $status): void {
            $payment = TerminalPayment::query()->lockForUpdate()->findOrFail($payment->id);
            $payment->update([
                'status' => $status, 'provider_payload' => $payload, 'checked_at' => now(),
                'paid_at' => $status === 'PAID' ? ($payment->paid_at ?: now()) : $payment->paid_at,
            ]);
            if ($status !== 'PAID' || $payment->applied_at) {
                return;
            }

            if (! $payment->expedition_registration_id) {
                $payment->update(['applied_at' => now()]);

                return;
            }
            $registration = $payment->registration()->lockForUpdate()->firstOrFail();
            $amountPaid = round((float) $registration->amount_paid + ($payment->amount / 100), 2);
            $amountDue = max(0, (float) $registration->amount_due - (float) $registration->discount_amount);
            $registration->update([
                'amount_paid' => $amountPaid,
                'payment_method' => PaymentMethod::Card,
                'payment_status' => $amountPaid >= $amountDue ? PaymentStatus::Paid : PaymentStatus::Deposit,
                'status' => $amountPaid >= $amountDue ? RegistrationStatus::Confirmed : $registration->status,
                'hold_expires_at' => $amountPaid >= $amountDue ? null : $registration->hold_expires_at,
            ]);
            $payment->update(['applied_at' => now()]);
        });

        return $status;
    }

    private function client()
    {
        return Http::withBasicAuth(config('shop.terminal.login'), config('shop.terminal.secret'))
            ->acceptJson()->asJson()->timeout(config('shop.terminal.timeout'));
    }

    private function url(string $path): string
    {
        return config('shop.terminal.api_url').$path;
    }
}
