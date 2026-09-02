<?php

namespace Tests\Feature;

use App\Models\Expedition;
use App\Models\ExpeditionRegistration;
use App\Services\ComgateTerminal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ComgateTerminalTest extends TestCase
{
    use RefreshDatabase;

    public function test_paid_terminal_transaction_is_applied_to_registration_once(): void
    {
        config([
            'shop.terminal.enabled' => true,
            'shop.terminal.login' => 'terminal-login',
            'shop.terminal.secret' => 'terminal-secret',
        ]);
        $registration = ExpeditionRegistration::query()->create([
            'expedition_id' => Expedition::default()->id,
            'mode' => 'reservation',
            'status' => 'approved',
            'payment_status' => 'unpaid',
            'name' => 'Jan Novák',
            'email' => 'jan@example.test',
            'party_size' => 1,
            'amount_due' => 1250,
            'amount_paid' => 0,
            'currency' => 'CZK',
            'consent_at' => now(),
            'consent_ip' => '127.0.0.1',
        ]);
        Http::fake([
            'https://payments.comgate.cz/v2.0/terminalPayment.json' => Http::response([
                'code' => 0,
                'message' => 'OK',
                'transId' => 'TERM-001',
            ]),
            'https://payments.comgate.cz/v2.0/terminalPayment/transId/TERM-001.json' => Http::response([
                'code' => 0,
                'message' => 'OK',
                'transId' => 'TERM-001',
                'price' => 125000,
                'curr' => 'CZK',
                'status' => 'PAID',
            ]),
        ]);

        $service = app(ComgateTerminal::class);
        $payment = $service->create($registration, 125000);

        $this->assertSame('PAID', $service->refresh($payment));
        $this->assertSame('PAID', $service->refresh($payment));
        $this->assertDatabaseHas('terminal_payments', ['transaction_id' => 'TERM-001', 'status' => 'PAID']);
        $this->assertDatabaseHas('expedition_registrations', [
            'id' => $registration->id,
            'payment_method' => 'card',
            'payment_status' => 'paid',
            'status' => 'confirmed',
            'amount_paid' => 1250,
        ]);
    }
}
