<?php

namespace App\Services;

use App\Models\ShopOrder;
use App\Models\ShopPayment;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ComgateGateway
{
    private string $endpoint = 'https://payments.comgate.cz/v1.0';

    public function configured(): bool
    {
        return filled(config('shop.comgate.merchant')) && filled(config('shop.comgate.secret'));
    }

    public function create(ShopOrder $order): ShopPayment
    {
        if (! $this->configured()) {
            throw new RuntimeException('Platební brána Comgate zatím není nakonfigurována.');
        }
        $response = Http::asForm()->accept('application/x-www-form-urlencoded')->timeout(15)->post($this->endpoint.'/create', [
            'merchant' => config('shop.comgate.merchant'), 'secret' => config('shop.comgate.secret'), 'test' => config('shop.comgate.test') ? 'true' : 'false', 'price' => $order->grand_total, 'curr' => $order->currency,
            'label' => mb_substr('Vino '.$order->number, 0, 16), 'refId' => $order->number, 'method' => 'ALL', 'email' => $order->email, 'phone' => $order->phone, 'fullName' => $order->customer_name,
            'billingAddrCity' => $order->billing_city, 'billingAddrStreet' => $order->billing_street, 'billingAddrPostalCode' => $order->billing_postcode, 'billingAddrCountry' => $order->billing_country,
            'delivery' => 'PICKUP', 'category' => 'PHYSICAL_GOODS_ONLY', 'prepareOnly' => 'true', 'lang' => 'cs', 'expirationTime' => '2d',
            'url_paid' => route('shop.payment.return', ['order' => $order, 'result' => 'paid']), 'url_cancelled' => route('shop.payment.return', ['order' => $order, 'result' => 'cancelled']), 'url_pending' => route('shop.payment.return', ['order' => $order, 'result' => 'pending']),
        ]);
        $response->throw();
        parse_str($response->body(), $payload);
        if ((string) ($payload['code'] ?? '') !== '0' || empty($payload['transId']) || empty($payload['redirect'])) {
            throw new RuntimeException('Comgate odmítl platbu: '.($payload['message'] ?? 'neznámá chyba'));
        }

        return $order->payments()->create(['transaction_id' => $payload['transId'], 'status' => 'pending', 'amount' => $order->grand_total, 'currency' => $order->currency, 'redirect_url' => $payload['redirect'], 'provider_payload' => $payload]);
    }

    public function refresh(ShopPayment $payment): string
    {
        $response = Http::asForm()->timeout(15)->post($this->endpoint.'/status', ['merchant' => config('shop.comgate.merchant'), 'secret' => config('shop.comgate.secret'), 'transId' => $payment->transaction_id]);
        $response->throw();
        parse_str($response->body(), $payload);
        if ((string) ($payload['code'] ?? '') !== '0') {
            throw new RuntimeException('Stav platby se nepodařilo ověřit.');
        }
        $order = $payment->order;
        if (($payload['refId'] ?? null) !== $order->number || (int) ($payload['price'] ?? -1) !== $payment->amount || ($payload['curr'] ?? null) !== $payment->currency) {
            throw new RuntimeException('Údaje platby neodpovídají objednávce.');
        }
        $status = strtolower($payload['status'] ?? 'pending');
        $payment->update(['status' => $status, 'provider_payload' => $payload, 'confirmed_at' => $status === 'paid' ? now() : null]);
        if ($status === 'paid' && $order->payment_status !== 'paid') {
            $order->update(['payment_status' => 'paid', 'status' => 'processing', 'paid_at' => now(), 'invoice_number' => 'FV-'.now()->format('Y').'-'.str_pad((string) $order->id, 6, '0', STR_PAD_LEFT)]);
            foreach ($order->items as $item) {
                $item->variant?->decrement('stock_quantity', $item->quantity);
                $item->variant?->decrement('reserved_quantity', $item->quantity);
            }
        } elseif ($status === 'cancelled' && $order->status !== 'cancelled') {
            $this->releaseReservations($order);
            $order->update(['payment_status' => 'cancelled', 'status' => 'cancelled']);
        }

        return $status;
    }

    public function releaseReservations(ShopOrder $order): void
    {
        foreach ($order->items()->with('variant')->get() as $item) {
            if ($item->variant) {
                $item->variant->decrement('reserved_quantity', min($item->quantity, $item->variant->reserved_quantity));
            }
        }
    }
}
