<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Expediční dispečink</x-slot>
        <x-slot name="description">Jedna společná aktivní etapa, poslední hlášení polohy a rychlé ovládání z telefonu.</x-slot>
        <div class="space-y-5">
            @if($active)
                <div class="rounded-xl bg-primary-50 p-5 ring-1 ring-primary-200 dark:bg-primary-950/30 dark:ring-primary-800">
                    <p class="text-xs font-bold uppercase tracking-wider text-primary-700 dark:text-primary-300">Právě teď · {{ $state->is_manual ? 'ručně potvrzeno' : 'odhad podle času' }}</p>
                    <h2 class="mt-2 text-2xl font-bold">{{ $active instanceof \App\Models\RoutePoint ? $active->name : ($active->name ?: $active->fromPoint->name.' → '.$active->toPoint->name) }}</h2>
                    @if(($position['source'] ?? null) === 'estimate')<p class="mt-2">Odhadovaný průběh přesunu: <strong>{{ $position['progress'] }} %</strong></p>@endif
                    @if(($position['source'] ?? null) === 'gps')<p class="mt-2">Poloha potvrzená členem expedice {{ $position['reportedAt']->diffForHumans() }}.</p>@endif
                </div>
            @else<p>Zatím není připravený žádný bod ani přesun.</p>@endif
            @if($position)
                @php($lat = (float) $position['latitude']) @php($lon = (float) $position['longitude'])
                <div class="overflow-hidden rounded-xl ring-1 ring-gray-200 dark:ring-white/10">
                    <iframe title="Mapa aktivní etapy" loading="lazy" class="h-72 w-full border-0" src="https://www.openstreetmap.org/export/embed.html?bbox={{ $lon - 0.08 }}%2C{{ $lat - 0.05 }}%2C{{ $lon + 0.08 }}%2C{{ $lat + 0.05 }}&amp;layer=mapnik&amp;marker={{ $lat }}%2C{{ $lon }}"></iframe>
                    <p class="px-3 py-2 text-xs">{{ ($position['source'] ?? null) === 'gps' ? 'Poslední potvrzená GPS poloha.' : 'Orientační poloha aktivní etapy podle itineráře.' }}</p>
                </div>
            @endif
            <div class="grid gap-3 sm:grid-cols-3">
                <x-filament::button tag="a" :href="route('tracking.location.create')" icon="heroicon-o-map-pin">Oznámit moji polohu</x-filament::button>
                <x-filament::button tag="a" :href="route('tracking.photo.create')" color="success" icon="heroicon-o-camera">Přidat fotku na mapu</x-filament::button>
                <x-filament::button wire:click="useAutomatic" color="gray" icon="heroicon-o-clock">Řídit podle času</x-filament::button>
            </div>
            <div class="overflow-x-auto rounded-xl ring-1 ring-gray-200 dark:ring-white/10"><table class="w-full text-sm">
                <thead><tr class="bg-gray-50 text-left dark:bg-white/5"><th class="p-3">Itinerář</th><th class="p-3">Čas</th><th class="p-3">Stav</th><th class="p-3"><span class="sr-only">Akce</span></th></tr></thead><tbody>
                @foreach($items as $item) @php($record = $item['record']) @php($isActive = $active && $active::class === $record::class && $active->getKey() === $record->getKey())
                    <tr @class(['border-t border-gray-200 dark:border-white/10', 'bg-primary-50 dark:bg-primary-950/30' => $isActive])>
                        <td class="p-3 font-medium">{{ $item['type'] === 'point' ? '● '.$record->name : $record->transport_mode->icon().' '.($record->name ?: $record->fromPoint->name.' → '.$record->toPoint->name) }}</td>
                        <td class="p-3">{{ $item['type'] === 'point' ? $record->occurred_at?->format('j. n. H:i') : $record->displayDeparture()?->format('j. n. H:i') }}</td><td class="p-3">{{ $record->status->label() }}</td>
                        <td class="p-3 text-right">@if(!$isActive)<x-filament::button size="sm" color="gray" wire:click="activate{{ $item['type'] === 'point' ? 'Point' : 'Segment' }}({{ $record->id }})">Jsme zde</x-filament::button>@else<strong>Aktivní</strong>@endif</td>
                    </tr>
                @endforeach
                </tbody></table></div>
            @if($locations->isNotEmpty())<div><h3 class="mb-2 font-bold">Poslední hlášení posádky</h3><div class="grid gap-2 sm:grid-cols-2">@foreach($locations as $location)<div class="rounded-lg bg-gray-50 p-3 dark:bg-white/5"><strong>{{ $location->user->name }}</strong><br><span class="text-sm">{{ $location->reported_at->diffForHumans() }} · přesnost {{ $location->accuracy_meters ? 'asi '.$location->accuracy_meters.' m' : 'neuvedena' }}</span></div>@endforeach</div></div>@endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
