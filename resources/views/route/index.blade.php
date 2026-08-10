@extends('layouts.app')

@section('title', 'Trasa a časová osa expedice — Slepé Slunce')
@section('description', 'Sledujte cestu expedice Slepé Slunce: zastávky, lety, jízdy autem a autobusem, fotografie, videa a aktuální polohu.')

@push('head')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" crossorigin="">
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" crossorigin="">
@endpush

@section('content')
    <header class="page-header route-header">
        <div class="shell">
            <p class="eyebrow">Expedice v pohybu</p>
            <h1>Trasa a časová osa</h1>
            <p>Zastávky, cíle a jednotlivé přesuny za úplným zatměním Slunce. Každý druh dopravy má vlastní podobu; přerušovaná čára znamená plánovanou cestu.</p>
        </div>
    </header>

    <section class="route-section light-section" aria-labelledby="mapa-nadpis">
        <div class="shell">
            <h2 id="mapa-nadpis" class="visually-hidden">Interaktivní mapa trasy</h2>
            @if($points->isEmpty())
                <div class="empty-state dark-empty">
                    <h2>Trasu právě připravujeme</h2>
                    <p>Jakmile přidáme první zastávky, objeví se tady mapa i jejich chronologický přehled.</p>
                </div>
            @else
                <div id="route-map" class="route-map" role="region" aria-label="Interaktivní mapa zastávek a přesunů expedice" tabindex="0"></div>
                <div class="route-legend" aria-label="Legenda mapy">
                    <span><i class="legend-line legend-line--completed" aria-hidden="true"></i> Dokončeno</span>
                    <span><i class="legend-line legend-line--progress" aria-hidden="true"></i> Právě cestujeme</span>
                    <span><i class="legend-line legend-line--planned" aria-hidden="true"></i> Plánováno</span>
                    <span>📍 Poloha člena</span><span>📷 Fotografie</span>
                </div>
                <p class="map-note">Mapa je doplňková. Stejné informace včetně časů, dopravy a médií jsou v přístupné časové ose níže.</p>
                <noscript><p class="map-warning">Interaktivní mapa vyžaduje JavaScript. Celou cestu najdete v časové ose níže.</p></noscript>
            @endif
        </div>
    </section>

    @if($points->isNotEmpty())
        <section class="route-list-section" aria-labelledby="casova-osa-nadpis">
            <div class="shell route-shell">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">Chronologicky a bez mapy</p>
                        <h2 id="casova-osa-nadpis">Časová osa cesty</h2>
                    </div>
                    <p>{{ $points->count() }} zastávek · {{ $segments->count() }} přesunů</p>
                </div>

                <ol class="route-timeline">
                    @foreach($timeline as $item)
                        @php($record = $item['record'])
                        @if($item['type'] === 'point')
                            <li class="route-stop route-stop--{{ $record->status->value }} @if($record->is_goal) route-stop--goal @endif">
                                <article>
                                    <div class="route-stop-meta">
                                        <span class="status-label">{{ $record->status->label() }}</span>
                                        @if($record->is_goal)<span class="goal-label">Cíl expedice</span>@endif
                                        @if($record->occurred_at)<time datetime="{{ $record->occurred_at->toIso8601String() }}">{{ $record->occurred_at->translatedFormat('j. n. Y H:i') }}</time>@endif
                                    </div>
                                    <h3>{{ $record->name }}</h3>
                                    @if($record->description)<p>{{ $record->description }}</p>@endif
                                    @include('route.partials.media', ['record' => $record, 'label' => 'zastávky '.$record->name])
                                    @if($record->post)<p><a class="text-link" href="{{ route('posts.show', $record->post) }}">Přečíst zápis z tohoto místa</a></p>@endif
                                </article>
                            </li>
                        @else
                            <li class="route-transfer route-transfer--{{ $record->status->value }}">
                                <article>
                                    <div class="route-transfer-heading">
                                        <span class="transport-icon" aria-hidden="true">{{ $record->transport_mode->icon() }}</span>
                                        <div>
                                            <p class="route-transfer-kicker">{{ $record->transport_mode->label() }} · {{ $record->status->label() }}</p>
                                            <h3>{{ $record->name ?: $record->fromPoint->name.' → '.$record->toPoint->name }}</h3>
                                        </div>
                                    </div>
                                    <dl class="route-transfer-facts">
                                        @if($record->displayDeparture())
                                            <div><dt>Odjezd / odlet</dt><dd><time datetime="{{ $record->displayDeparture()->toIso8601String() }}">{{ $record->displayDeparture()->translatedFormat('j. n. Y H:i') }}</time>@if($record->departed_at) <span>(skutečnost)</span>@endif</dd></div>
                                        @endif
                                        @if($record->displayArrival())
                                            <div><dt>Příjezd / přílet</dt><dd><time datetime="{{ $record->displayArrival()->toIso8601String() }}">{{ $record->displayArrival()->translatedFormat('j. n. Y H:i') }}</time>@if($record->arrived_at) <span>(skutečnost)</span>@endif</dd></div>
                                        @endif
                                        @if($record->distance_km)<div><dt>Vzdálenost</dt><dd>{{ number_format((float) $record->distance_km, 1, ',', ' ') }} km</dd></div>@endif
                                        @if($record->displayDuration())
                                            @php($duration = $record->displayDuration())
                                            <div><dt>Doba cesty</dt><dd>@if($duration >= 60){{ intdiv($duration, 60) }} h @if($duration % 60){{ $duration % 60 }} min @endif @else {{ $duration }} min @endif</dd></div>
                                        @endif
                                        @if($record->provider)<div><dt>Dopravce</dt><dd>{{ $record->provider }}</dd></div>@endif
                                        @if($record->reference)<div><dt>Spoj</dt><dd>{{ $record->reference }}</dd></div>@endif
                                    </dl>
                                    @if($record->description)<p>{{ $record->description }}</p>@endif
                                    @include('route.partials.media', ['record' => $record, 'label' => 'přesunu '.$record->fromPoint->name.' do '.$record->toPoint->name])
                                    @if($record->post)<p><a class="text-link" href="{{ route('posts.show', $record->post) }}">Přečíst zápis z tohoto přesunu</a></p>@endif
                                </article>
                            </li>
                        @endif
                    @endforeach
                </ol>
            </div>
        </section>
    @endif
@endsection

@if($points->isNotEmpty())
@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js" crossorigin=""></script>
    <script>
        (() => {
            const element = document.getElementById('route-map');
            if (!element || typeof L === 'undefined') return;

            const points = {{ Illuminate\Support\Js::from($mapPoints) }};
            const segments = {{ Illuminate\Support\Js::from($mapSegments) }};
            const photos = {{ Illuminate\Support\Js::from($mapPhotos) }};
            const members = {{ Illuminate\Support\Js::from($memberLocations) }};
            const activePosition = {{ Illuminate\Support\Js::from($position) }};
            const map = L.map(element, { scrollWheelZoom: false });
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(map);

            const transportColors = { flight: '#2c5d9b', bus: '#8a4d0f', car: '#17150f', train: '#7b2f72', walk: '#347442', bicycle: '#217477', ferry: '#246e99', other: '#655f54' };
            const allCoordinates = [];

            segments.forEach(segment => {
                if (!Array.isArray(segment.geometry) || segment.geometry.length < 2) return;
                segment.geometry.forEach(coordinate => allCoordinates.push(coordinate));
                const style = {
                    color: transportColors[segment.transport] || transportColors.other,
                    weight: segment.isActive ? 8 : (segment.status === 'in_progress' ? 7 : 5),
                    opacity: segment.status === 'planned' ? .75 : 1,
                    dashArray: segment.status === 'planned' ? '10 11' : null,
                    lineCap: 'round'
                };
                const line = L.polyline(segment.geometry, style).addTo(map);
                const popup = document.createElement('div');
                popup.className = 'map-popup map-popup--segment';
                const title = document.createElement('strong');
                title.textContent = `${segment.transportIcon} ${segment.name}`;
                popup.append(title);
                const state = document.createElement('span');
                state.textContent = `${segment.transportLabel} · ${segment.statusLabel}`;
                popup.append(state);
                const facts = [segment.departure, segment.distance, segment.duration].filter(Boolean);
                if (facts.length) {
                    const meta = document.createElement('p');
                    meta.textContent = facts.join(' · ');
                    popup.append(meta);
                }
                if (segment.image) {
                    const image = document.createElement('img');
                    image.src = segment.image;
                    image.alt = segment.imageAlt || '';
                    image.loading = 'lazy';
                    popup.append(image);
                }
                if (segment.description) {
                    const description = document.createElement('p');
                    description.textContent = segment.description;
                    popup.append(description);
                }
                if (segment.postUrl) {
                    const link = document.createElement('a');
                    link.href = segment.postUrl;
                    link.textContent = 'Přečíst zápis';
                    popup.append(link);
                }
                line.bindPopup(popup).bindTooltip(`${segment.transportIcon} ${segment.fromName} → ${segment.toName}`);
            });

            points.forEach((point, index) => {
                allCoordinates.push([point.latitude, point.longitude]);
                const marker = L.circleMarker([point.latitude, point.longitude], {
                    radius: point.isActive ? 14 : (point.isGoal ? 11 : 8),
                    color: '#17150f', weight: 3,
                    fillColor: point.status === 'current' ? '#f4c542' : (point.status === 'visited' ? '#ffffff' : '#c7b978'),
                    fillOpacity: 1
                }).addTo(map);
                const popup = document.createElement('div');
                popup.className = 'map-popup';
                const title = document.createElement('strong');
                title.textContent = point.name;
                popup.append(title);
                const state = document.createElement('span');
                state.textContent = point.statusLabel + (point.isGoal ? ' · Cíl expedice' : '');
                popup.append(state);
                if (point.image) {
                    const image = document.createElement('img');
                    image.src = point.image;
                    image.alt = point.imageAlt || '';
                    image.loading = 'lazy';
                    popup.append(image);
                }
                if (point.description) {
                    const description = document.createElement('p');
                    description.textContent = point.description;
                    popup.append(description);
                }
                if (point.postUrl) {
                    const link = document.createElement('a');
                    link.href = point.postUrl;
                    link.textContent = 'Přečíst zápis';
                    popup.append(link);
                }
                marker.bindPopup(popup).bindTooltip(`${index + 1}. ${point.name}`);
            });

            if (typeof L.markerClusterGroup === 'function') {
                const photoLayer = L.markerClusterGroup({showCoverageOnHover:false, maxClusterRadius:55});
                photos.forEach(photo => {
                    const marker = L.marker([photo.latitude, photo.longitude], {title: photo.alt});
                    const popup = document.createElement('div'); popup.className='map-popup map-photo-popup';
                    const image=document.createElement('img'); image.src=photo.image; image.alt=photo.alt; image.loading='lazy'; popup.append(image);
                    if(photo.caption){const caption=document.createElement('p');caption.textContent=photo.caption;popup.append(caption)}
                    const meta=document.createElement('span');meta.textContent=[photo.author,photo.takenAt].filter(Boolean).join(' · ');popup.append(meta);marker.bindPopup(popup);photoLayer.addLayer(marker);
                }); map.addLayer(photoLayer);
            }
            members.forEach(member => {
                const marker=L.circleMarker([member.latitude,member.longitude],{radius:9,color:'#fff',weight:3,fillColor:member.stale?'#777':'#347442',fillOpacity:1}).addTo(map);
                marker.bindTooltip(`${member.name} · ${member.age}`).bindPopup(`<strong>${member.name}</strong><p>Poloha hlášena ${member.reportedAt}. Veřejně je zobrazena pouze přibližně.</p>`);
            });

            if (activePosition) {
                const current=L.circleMarker([activePosition.latitude,activePosition.longitude],{radius:12,color:'#17150f',weight:4,fillColor:'#f4c542',fillOpacity:1}).addTo(map);
                current.bindTooltip(activePosition.source==='gps'?'Poslední potvrzená poloha':'Odhadovaná poloha podle itineráře');
            }

            if (activePosition) map.setView([activePosition.latitude, activePosition.longitude], activePosition.source === 'point' ? 11 : 8);
            else if (allCoordinates.length === 1) map.setView(allCoordinates[0], 10);
            else map.fitBounds(L.latLngBounds(allCoordinates), { padding: [35, 35], maxZoom: 12 });
        })();
    </script>
@endpush
@endif
