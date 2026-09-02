<?php

namespace App\Http\Controllers;

use App\Models\ShopOrder;
use App\Models\ShopPayment;
use App\Models\WineVariant;
use App\Services\ComgateGateway;
use App\Services\ShopCart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ShopCheckoutController extends Controller
{
    public function create(ShopCart $cart): View
    {
        abort_if($cart->lines()->isEmpty(), 404);

        return view('shop.checkout', compact('cart'));
    }

    public function store(Request $request, ShopCart $cart, ComgateGateway $gateway): RedirectResponse
    {
        $data = $request->validate(['customer_name' => ['required', 'string', 'max:190'], 'email' => ['required', 'email:rfc', 'max:190'], 'phone' => ['nullable', 'string', 'max:50'], 'billing_street' => ['required', 'string', 'max:250'], 'billing_city' => ['required', 'string', 'max:160'], 'billing_postcode' => ['required', 'string', 'max:30'], 'billing_country' => ['required', 'in:CZ,SK,AT,DE'], 'note' => ['nullable', 'string', 'max:2000'], 'payment_method' => ['required', 'in:online_card'], 'age_confirmed' => ['accepted'], 'terms' => ['accepted'], 'privacy_consent' => ['accepted']]);
        unset($data['payment_method']);
        $lines = $cart->lines();
        abort_if($lines->isEmpty(), 422);
        $order = DB::transaction(function () use ($data, $lines, $cart) {
            foreach ($lines as $line) {
                $variant = WineVariant::query()->lockForUpdate()->findOrFail($line['variant']->id);
                if ($variant->availableStock() < $line['quantity']) {
                    abort(409, 'Některé víno už není v požadovaném množství skladem.');
                }$variant->increment('reserved_quantity', $line['quantity']);
            }$number = 'SS'.now()->format('ymd').Str::upper(Str::random(5));
            $order = ShopOrder::query()->create([...$data, 'number' => $number, 'currency' => $cart->currency(), 'subtotal' => $cart->total(), 'grand_total' => $cart->total(), 'delivery_method' => 'pickup', 'access_token' => Str::random(64), 'consent_at' => now()]);
            foreach ($lines as $line) {
                $order->items()->create(['wine_variant_id' => $line['variant']->id, 'sku' => $line['variant']->sku, 'name' => $line['variant']->product->name.' '.($line['variant']->vintage ?: ''), 'quantity' => $line['quantity'], 'unit_price' => $line['unit_price'], 'vat_rate' => $line['variant']->vat_rate, 'total' => $line['total']]);
            }

            return $order;
        });
        $cart->clear();
        if (! $gateway->configured()) {
            return redirect()->route('shop.order', $order)->with('message', 'Objednávka je přijata. Platební údaje doplníme po aktivaci Comgate.');
        }
        try {
            $payment = $gateway->create($order);

            return redirect()->away($payment->redirect_url);
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()->route('shop.order', $order)->with('message', 'Objednávka je uložená, ale platbu se nepodařilo zahájit. Zkuste to později nebo nás kontaktujte.');
        }
    }

    public function show(ShopOrder $order): View
    {
        return view('shop.order', compact('order'));
    }

    public function paymentReturn(Request $request, ShopOrder $order, ComgateGateway $gateway): RedirectResponse
    {
        $payment = $order->payments()->latest()->first();
        if ($payment) {
            try {
                $gateway->refresh($payment);
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        return redirect()->route('shop.order', $order);
    }

    public function invoice(ShopOrder $order, string $token): View
    {
        abort_unless(hash_equals($order->access_token, $token) && $order->invoice_number, 404);

        return view('shop.invoice', compact('order'));
    }

    public function callback(Request $request, ComgateGateway $gateway): Response
    {
        $payment = ShopPayment::query()->where('transaction_id', $request->input('transId'))->firstOrFail();
        try {
            $gateway->refresh($payment);

            return response('code=0&message=OK', 200, ['Content-Type' => 'application/x-www-form-urlencoded']);
        } catch (\Throwable $exception) {
            report($exception);

            return response('code=1500&message=verification_failed', 500, ['Content-Type' => 'application/x-www-form-urlencoded']);
        }
    }
}
