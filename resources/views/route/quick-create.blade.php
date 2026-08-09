<!doctype html>
<html lang="cs">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rychle přidat bod trasy — Slepé Slunce</title>
    <meta name="theme-color" content="#17150f">
    <link rel="stylesheet" href="{{ asset('assets/site.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/route.css') }}">
</head>
<body class="quick-route-page">
    <a class="skip-link" href="#formular">Přeskočit na formulář</a>
    <main class="quick-route-shell">
        <p class="eyebrow">Rychlý zápis z telefonu</p>
        <h1>Přidat bod trasy</h1>
        <p>Uložte místo během cesty. Fotografie, video nebo celý článek můžete připojit později v administraci.</p>

        @if(session('status'))<div class="quick-message" role="status">{{ session('status') }}</div>@endif
        @if($errors->any())
            <div class="quick-errors" role="alert" tabindex="-1">
                <strong>Bod se nepodařilo uložit. Opravte označená pole.</strong>
                <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <form id="formular" class="quick-route-form" method="post" action="{{ route('route.quick.store') }}">
            @csrf
            <div><label for="name">Název místa</label><input id="name" name="name" value="{{ old('name') }}" required maxlength="160" autocomplete="off"></div>
            <div><label for="description">Krátký popis <span>(nepovinné)</span></label><textarea id="description" name="description" rows="3" maxlength="700">{{ old('description') }}</textarea></div>

            <button id="use-location" class="button button-primary location-button" type="button">Použít moji aktuální polohu</button>
            <p id="location-status" class="location-status" aria-live="polite">Souřadnice můžete případně vyplnit také ručně.</p>

            <div class="coordinate-grid">
                <div><label for="latitude">Zeměpisná šířka</label><input id="latitude" name="latitude" inputmode="decimal" value="{{ old('latitude') }}" required></div>
                <div><label for="longitude">Zeměpisná délka</label><input id="longitude" name="longitude" inputmode="decimal" value="{{ old('longitude') }}" required></div>
            </div>
            <div><label for="status">Stav bodu</label><select id="status" name="status" required>@foreach($statuses as $value => $label)<option value="{{ $value }}" @selected(old('status', 'current') === $value)>{{ $label }}</option>@endforeach</select></div>
            <label class="checkbox-row"><input type="checkbox" name="is_goal" value="1" @checked(old('is_goal'))> Toto místo je důležitý cíl expedice</label>
            <button class="button button-primary" type="submit">Uložit bod trasy</button>
        </form>
        <p class="quick-back"><a href="{{ \App\Filament\Resources\RoutePoints\RoutePointResource::getUrl('index') }}">Zpět do správy trasy</a></p>
    </main>
    <script>
        (() => {
            const button = document.getElementById('use-location');
            const status = document.getElementById('location-status');
            button.addEventListener('click', () => {
                if (!navigator.geolocation) {
                    status.textContent = 'Tento prohlížeč neumí zjistit polohu. Vyplňte souřadnice ručně.';
                    return;
                }
                button.disabled = true;
                status.textContent = 'Zjišťuji vaši polohu…';
                navigator.geolocation.getCurrentPosition(position => {
                    document.getElementById('latitude').value = position.coords.latitude.toFixed(7);
                    document.getElementById('longitude').value = position.coords.longitude.toFixed(7);
                    status.textContent = `Poloha je načtená s přesností přibližně ${Math.round(position.coords.accuracy)} metrů.`;
                    button.disabled = false;
                }, error => {
                    status.textContent = error.code === 1 ? 'Přístup k poloze nebyl povolen. Souřadnice můžete vyplnit ručně.' : 'Polohu se nepodařilo zjistit. Zkuste to znovu nebo ji vyplňte ručně.';
                    button.disabled = false;
                }, { enableHighAccuracy: true, timeout: 15000, maximumAge: 30000 });
            });
        })();
    </script>
</body>
</html>
