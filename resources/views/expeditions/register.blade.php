@extends('layouts.app')

@section('title', (app()->isLocale('en') ? 'Apply' : 'Přihláška').' — '.$expedition->name)
@section('content')
<header class="page-header"><div class="shell"><p class="eyebrow">{{ $expedition->name }}</p><h1>{{ app()->isLocale('en') ? 'Expedition application' : 'Přihláška' }}</h1><p>{{ app()->isLocale('en') ? 'Submitting this form creates a request, not a confirmed booking. The expedition leader will contact you.' : 'Odesláním vznikne žádost, ne automaticky potvrzená rezervace. Vedoucí expedice se vám ozve.' }}</p></div></header>
<section class="section light-section"><div class="shell narrow-section">
    @if($errors->any())<div class="error-summary" role="alert" tabindex="-1"><h2>{{ app()->isLocale('en') ? 'Please check the form' : 'Formulář obsahuje chyby' }}</h2><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form class="accessible-form" method="post" action="{{ route('expeditions.register.store', $expedition) }}">@csrf
        <fieldset><legend>{{ app()->isLocale('en') ? 'Application type' : 'Typ přihlášení' }}</legend>
            @foreach($expedition->allowed_registration_modes as $mode)
                @php($registrationMode = App\Enums\RegistrationMode::from($mode))
                <label class="check-label"><input type="radio" name="mode" value="{{ $mode }}" required @checked(old('mode') === $mode)> {{ app()->isLocale('en') ? (['interest' => 'Expression of interest', 'application' => 'Application to join', 'reservation' => 'Place reservation'][$mode] ?? $registrationMode->label()) : $registrationMode->label() }}</label>
            @endforeach
        </fieldset>
        <fieldset><legend>{{ app()->isLocale('en') ? 'Preferred payment method' : 'Preferovaný způsob platby' }}</legend>
            @foreach(App\Enums\PaymentMethod::cases() as $paymentMethod)
                @php($paymentAvailable = $paymentMethod->available() && in_array($paymentMethod->value, $expedition->allowed_payment_methods ?? [], true))
                <label class="check-label @if(!$paymentAvailable) disabled-option @endif">
                    <input type="radio" name="payment_method" value="{{ $paymentMethod->value }}" required
                        @checked(old('payment_method') === $paymentMethod->value)
                        @disabled(!$paymentAvailable)>
                    {{ app()->isLocale('en') ? (['cash' => 'Cash on site', 'bank_transfer' => 'Bank transfer', 'card' => 'Payment card'][$paymentMethod->value] ?? $paymentMethod->label()) : $paymentMethod->label() }}
                    @if(!$paymentAvailable)<span class="optional">{{ app()->isLocale('en') ? '(not available yet)' : '(zatím není aktivní)' }}</span>@endif
                </label>
            @endforeach
            <p class="field-help">{{ app()->isLocale('en') ? 'This is a preference. Payment instructions will follow only after the organiser confirms your place.' : 'Jde o preferenci. Pokyny k úhradě obdržíte až po potvrzení místa pořadatelem.' }}</p>
        </fieldset>
        <div class="form-grid">
            <div><label for="registration-name">{{ app()->isLocale('en') ? 'Full name' : 'Jméno a příjmení' }}</label><input id="registration-name" name="name" autocomplete="name" required value="{{ old('name') }}"></div>
            <div><label for="registration-email">E-mail</label><input id="registration-email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}"></div>
            <div><label for="registration-phone">{{ app()->isLocale('en') ? 'Phone' : 'Telefon' }} <span class="optional">{{ app()->isLocale('en') ? '(optional)' : '(nepovinné)' }}</span></label><input id="registration-phone" name="phone" type="tel" autocomplete="tel" value="{{ old('phone') }}"></div>
            <div><label for="party-size">{{ app()->isLocale('en') ? 'Number of people' : 'Počet osob' }}</label><input id="party-size" name="party_size" type="number" min="1" max="20" required value="{{ old('party_size', 1) }}">@if($expedition->availablePlaces() !== null)<p class="field-help">{{ app()->isLocale('en') ? 'Places currently available:' : 'Aktuálně' }} {{ $expedition->availablePlaces() }} {{ app()->isLocale('en') ? '' : 'volných míst.' }}</p>@endif</div>
        </div>
        <label for="departure-choice">{{ app()->isLocale('en') ? 'Departure point or transport preference' : 'Místo nástupu nebo preference dopravy' }} <span class="optional">{{ app()->isLocale('en') ? '(optional)' : '(nepovinné)' }}</span></label><input id="departure-choice" name="departure_choice" value="{{ old('departure_choice') }}">
        <label for="assistance-needs">{{ app()->isLocale('en') ? 'Assistance and accessibility needs' : 'Potřeby asistence a přístupnosti' }} <span class="optional">{{ app()->isLocale('en') ? '(optional)' : '(nepovinné)' }}</span></label><textarea id="assistance-needs" name="assistance_needs" rows="4">{{ old('assistance_needs') }}</textarea>
        <label for="dietary-needs">{{ app()->isLocale('en') ? 'Dietary requirements' : 'Stravovací omezení' }} <span class="optional">{{ app()->isLocale('en') ? '(optional)' : '(nepovinné)' }}</span></label><textarea id="dietary-needs" name="dietary_needs" rows="3">{{ old('dietary_needs') }}</textarea>
        <label for="registration-note">{{ app()->isLocale('en') ? 'Note' : 'Poznámka' }} <span class="optional">{{ app()->isLocale('en') ? '(optional)' : '(nepovinné)' }}</span></label><textarea id="registration-note" name="note" rows="4">{{ old('note') }}</textarea>
        <label class="check-label"><input type="checkbox" name="privacy_consent" value="1" required> {{ app()->isLocale('en') ? 'I agree to the processing of my details for this application. They will be kept for no longer than two years unless required by law.' : 'Souhlasím se zpracováním údajů pro vyřízení účasti. Údaje budou uchovány nejdéle 2 roky, nevyžaduje-li zákon déle.' }}</label>
        <div class="honeypot" aria-hidden="true"><label for="registration-website">Web</label><input id="registration-website" name="website" tabindex="-1" autocomplete="off"></div>
        <button class="button button-primary" type="submit">{{ app()->isLocale('en') ? 'Send application' : 'Odeslat žádost o rezervaci' }}</button>
    </form>
</div></section>
@endsection
