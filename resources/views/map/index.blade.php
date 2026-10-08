@extends('layouts.app')
@section('title', app()->isLocale('en') ? ($selectedExpedition ? 'Map — '.$selectedExpedition->name.' — Blind Sun' : 'Expedition map — Blind Sun') : (($selectedExpedition ? 'Mapa — '.$selectedExpedition->name : 'Mapa expedic').' — Slepé Slunce'))
@section('description', app()->isLocale('en') ? 'Explore the places, photos, stories and routes of Blind Sun expeditions.' : 'Místa, fotografie, články a trasy expedic Slepého slunce na jedné mapě.')
@push('head')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" crossorigin="">
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" crossorigin="">
<link rel="stylesheet" href="{{ asset('assets/atlas.css') }}">
@endpush
@section('content')
@php($isEnglish = app()->isLocale('en'))
<div class="atlas">
    <header class="atlas-intro shell">
        <p class="eyebrow">{{ $isEnglish ? 'Journeys in one place' : 'Cesty na jednom místě' }}</p>
        <h1>{{ $isEnglish ? ($selectedExpedition ? $selectedExpedition->name.' expedition map' : 'Explore our expeditions') : ($selectedExpedition ? 'Mapa expedice '.$selectedExpedition->name : 'Mapa našich expedic') }}</h1>
        <p>{{ $isEnglish ? 'Explore places, photos and stories. Choose an expedition or discover every journey.' : 'Projdi místa, fotky a příběhy. Vyber si expedici nebo se vydej napříč všemi cestami.' }}</p>
    </header>
    <div class="atlas-workspace">
        <aside class="atlas-sidebar" aria-label="{{ $isEnglish ? 'Filters and timeline' : 'Výběr a časová osa' }}">
            <form method="get" action="{{ route('map.index') }}" class="atlas-selector">
                <label for="atlas-expedition">{{ $isEnglish ? 'Expedition' : 'Expedice' }}</label>
                <select id="atlas-expedition" name="expedition" onchange="this.form.submit()">
                    <option value="">{{ $isEnglish ? 'All expeditions' : 'Všechny expedice' }}</option>
                    @foreach($expeditions as $expedition)
                        <option value="{{ $expedition->slug }}" @selected($selectedExpedition?->is($expedition))>{{ $expedition->name }}</option>
                    @endforeach
                </select>
                <noscript><button type="submit">{{ $isEnglish ? 'Show' : 'Zobrazit' }}</button></noscript>
            </form>
            <fieldset class="atlas-filters"><legend>{{ $isEnglish ? 'Show on the map' : 'Zobrazit na mapě' }}</legend>
                <label><input type="checkbox" data-filter="places" checked> <span>{{ $isEnglish ? 'Places' : 'Místa' }}</span></label>
                <label><input type="checkbox" data-filter="photos" checked> <span>{{ $isEnglish ? 'Photos' : 'Fotky' }}</span></label>
                <label><input type="checkbox" data-filter="articles" checked> <span>{{ $isEnglish ? 'Stories' : 'Články' }}</span></label>
                <label><input type="checkbox" id="atlas-routes" checked> <span>{{ $isEnglish ? 'Routes' : 'Trasy' }}</span></label>
            </fieldset>
            <div class="atlas-timeline-heading"><h2>{{ $isEnglish ? 'Timeline' : 'Časová osa' }}</h2><span id="atlas-count">{{ $items->count() }} {{ $isEnglish ? ($items->count() === 1 ? 'item' : 'items') : 'položek' }}</span></div>
            <ol class="atlas-timeline" id="atlas-timeline">
                @forelse($items as $index => $item)
                    <li data-type="{{ $item['type'] }}">
                        <button type="button" class="atlas-entry" data-index="{{ $index }}">
                            <span class="atlas-entry-type">{{ ($isEnglish ? ['places' => 'Place', 'photos' => 'Photo', 'articles' => 'Story'] : ['places' => 'Místo', 'photos' => 'Fotka', 'articles' => 'Článek'])[$item['type']] }} · {{ $item['expedition'] }}</span>
                            <strong>{{ $item['name'] }}</strong>
                            @if($item['dateLabel'])<time datetime="{{ $item['date'] }}">{{ $item['dateLabel'] }}</time>@endif
                        </button>
                        @if($item['type'] === 'photos' && $item['description'])<p class="atlas-entry-story">{{ $item['description'] }}</p>@endif
                        @if($item['url'])<a href="{{ $item['url'] }}" class="atlas-entry-link">{{ $isEnglish ? ($item['type'] === 'photos' ? 'View photo' : ($item['type'] === 'articles' ? 'Read story' : 'Open place')) : ($item['type'] === 'photos' ? 'Zobrazit fotografii' : ($item['type'] === 'articles' ? 'Otevřít článek' : 'Otevřít místo')) }} →</a>@endif
                    </li>
                @empty
                    <li class="atlas-empty">{{ $isEnglish ? 'No places yet. New locations and photos will appear here.' : 'Zatím tu nejsou žádné body. Jakmile přidáme polohu či fotku, uvidíš ji tady.' }}</li>
                @endforelse
            </ol>
        </aside>
        <div class="atlas-map-wrap">
            <div id="atlas-map" role="region" aria-label="{{ $isEnglish ? 'Interactive expedition map; all places are also listed in the timeline' : 'Interaktivní mapa expedic; všechna místa jsou také v časové ose' }}" tabindex="0"></div>
            <div class="atlas-map-note">{{ $isEnglish ? 'Zoom in to separate grouped markers. Every item is also listed in the timeline.' : 'Přiblížením rozbalíš shluky bodů. Všechny položky najdeš také v časové ose.' }}</div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js" crossorigin=""></script>
<script>
(() => {
    const items = {{ Illuminate\Support\Js::from($items) }};
    const segments = {{ Illuminate\Support\Js::from($segments) }};
    const map = L.map('atlas-map', {scrollWheelZoom: false, zoomControl: false});
    L.control.zoom({position: 'topright'}).addTo(map);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {maxZoom: 19, attribution: '&copy; OpenStreetMap'}).addTo(map);
    const clusters = L.markerClusterGroup({showCoverageOnHover: false, spiderfyOnMaxZoom: true, zoomToBoundsOnClick: true, maxClusterRadius: 58});
    const markers = new Map();
    const routeLayer = L.layerGroup().addTo(map);
    const kinds = {{ \Illuminate\Support\Js::from($isEnglish ? ['places' => 'Place', 'photos' => 'Photo', 'articles' => 'Story'] : ['places' => 'Místo', 'photos' => 'Fotka', 'articles' => 'Článek']) }};
    const labels = {{ \Illuminate\Support\Js::from($isEnglish ? ['viewPhoto' => 'View full-size photo', 'open' => 'Open', 'items' => 'items', 'item' => 'item'] : ['viewPhoto' => 'Zobrazit fotografii ve velkém', 'open' => 'Otevřít', 'items' => 'položek', 'item' => 'položka']) }};
    const colors = {places: '#f0ba36', photos: '#d07753', articles: '#93cbb4'};
    const text = (tag, value) => { const el = document.createElement(tag); el.textContent = value; return el; };
    const bounds = [];
    items.forEach((item, index) => {
        if (!Number.isFinite(item.latitude) || !Number.isFinite(item.longitude)) return;
        const marker = L.marker([item.latitude, item.longitude], {icon: L.divIcon({className: 'atlas-pin', html: `<span style="--pin:${colors[item.type]}">${{places:'●',photos:'▣',articles:'✦'}[item.type]}</span>`, iconSize: [38, 38], iconAnchor: [19, 19]}), title: item.name});
        const popup = document.createElement('div'); popup.className = 'atlas-popup';
        popup.append(text('small', `${kinds[item.type]} · ${item.expedition}${item.dateLabel ? ' · '+item.dateLabel : ''}`));
        popup.append(text('strong', item.name));
        if (item.image) { const image = document.createElement('img'); image.src = item.image; image.alt = item.alt || ''; image.loading = 'lazy';
            if (item.type === 'photos' && item.url) { const imageLink = document.createElement('a'); imageLink.href = item.url; imageLink.setAttribute('aria-label', labels.viewPhoto); imageLink.append(image); popup.append(imageLink); }
            else popup.append(image); }
        if (item.description && item.description !== item.name) popup.append(text('p', item.description));
        if (item.url) { const link = text('a', item.type === 'photos' ? labels.viewPhoto+' →' : labels.open+' →'); link.href = item.url; popup.append(link); }
        marker.bindPopup(popup); markers.set(index, marker); bounds.push([item.latitude, item.longitude]);
    });
    segments.forEach(segment => {
        if (!Array.isArray(segment.geometry) || segment.geometry.length < 2) return;
        const line = L.polyline(segment.geometry, {color: segment.status === 'completed' ? '#436b57' : '#d5a337', weight: 4, opacity: .82, dashArray: segment.status === 'planned' ? '9 9' : null});
        line.bindTooltip(`${segment.transport} · ${segment.name}`); line.addTo(routeLayer);
        segment.geometry.forEach(coordinate => bounds.push(coordinate));
    });
    const checks = Array.from(document.querySelectorAll('[data-filter]'));
    const refresh = () => {
        const visible = new Set(checks.filter(check => check.checked).map(check => check.dataset.filter));
        clusters.clearLayers();
        markers.forEach((marker, index) => { if (visible.has(items[index].type)) clusters.addLayer(marker); });
        document.querySelectorAll('#atlas-timeline li[data-type]').forEach(row => { row.hidden = !visible.has(row.dataset.type); });
        document.getElementById('atlas-count').textContent = `${items.filter(item => visible.has(item.type)).length} ${items.filter(item => visible.has(item.type)).length === 1 ? labels.item : labels.items}`;
        if (document.getElementById('atlas-routes').checked) routeLayer.addTo(map); else map.removeLayer(routeLayer);
    };
    checks.forEach(check => check.addEventListener('change', refresh));
    document.getElementById('atlas-routes').addEventListener('change', refresh);
    map.addLayer(clusters); refresh();
    document.querySelectorAll('.atlas-entry[data-index]').forEach(button => button.addEventListener('click', () => {
        const marker = markers.get(Number(button.dataset.index)); if (!marker) return;
        if (!clusters.hasLayer(marker)) { const check = document.querySelector(`[data-filter="${items[Number(button.dataset.index)].type}"]`); check.checked = true; refresh(); }
        clusters.zoomToShowLayer(marker, () => marker.openPopup());
        document.getElementById('atlas-map').scrollIntoView({behavior: 'smooth', block: 'nearest'});
    }));
    if (bounds.length > 1) map.fitBounds(bounds, {padding: [45, 45], maxZoom: 11});
    else if (bounds.length === 1) map.setView(bounds[0], 11);
    else map.setView([49.8, 15.5], 7);
})();
</script>
@endpush
