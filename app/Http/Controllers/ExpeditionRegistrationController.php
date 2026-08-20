<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Enums\RegistrationMode;
use App\Models\Expedition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ExpeditionRegistrationController extends Controller
{
    public function create(Expedition $expedition): View
    {
        abort_unless($expedition->acceptsRegistrations(), 404);

        return view('expeditions.register', compact('expedition'));
    }

    public function store(Request $request, Expedition $expedition): RedirectResponse
    {
        abort_unless($expedition->acceptsRegistrations(), 404);
        $allowedModes = $expedition->allowed_registration_modes ?? [];
        $allowedPaymentMethods = collect($expedition->allowed_payment_methods ?? [])
            ->filter(fn (string $method): bool => PaymentMethod::tryFrom($method)?->available() === true)
            ->values()
            ->all();
        $data = $request->validate([
            'mode' => ['required', Rule::in($allowedModes)],
            'name' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email:rfc', 'max:190'],
            'phone' => ['nullable', 'string', 'max:50'],
            'party_size' => ['required', 'integer', 'min:1', 'max:20'],
            'payment_method' => ['required', Rule::in($allowedPaymentMethods)],
            'departure_choice' => ['nullable', 'string', 'max:250'],
            'assistance_needs' => ['nullable', 'string', 'max:3000'],
            'dietary_needs' => ['nullable', 'string', 'max:2000'],
            'note' => ['nullable', 'string', 'max:3000'],
            'privacy_consent' => ['accepted'],
            'website' => ['nullable', 'max:0'],
        ]);
        if ($expedition->availablePlaces() !== null && $data['party_size'] > $expedition->availablePlaces()) {
            return back()->withErrors(['party_size' => 'Pro zvolený počet osob už není dostatek volných míst.'])->withInput();
        }
        unset($data['privacy_consent'], $data['website']);
        $registration = $expedition->registrations()->create([
            ...$data,
            'mode' => RegistrationMode::from($data['mode']),
            'consent_at' => now(),
            'consent_ip' => $request->ip(),
        ]);

        return redirect()->route('expeditions.show', $expedition)
            ->with('message', "Děkujeme. Přihlášku č. {$registration->id} jsme přijali a vedoucí expedice ji posoudí.");
    }
}
