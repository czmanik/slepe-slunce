@extends('layouts.app')

@section('title', 'Expedice — Slepé Slunce')
@section('description', 'Proběhlé i připravované přístupné expedice projektu Slepé Slunce.')

@section('content')
<header class="page-header"><div class="shell"><p class="eyebrow">Cestujeme spolu</p><h1>Expedice</h1><p>Každá cesta má vlastní program, tým, trasu, deník a podle nastavení také přihlášku.</p></div></header>
<section class="section light-section"><div class="shell expedition-cards">
    @forelse($expeditions as $expedition)
        <article class="expedition-card">
            <p class="status-pill status-pill--{{ $expedition->status()->value }}">{{ $expedition->status()->label() }}</p>
            <h2><a href="{{ route('expeditions.show', $expedition) }}">{{ $expedition->name }}</a></h2>
            @if($expedition->start_at)<p class="expedition-date"><time datetime="{{ $expedition->start_at->toDateString() }}">{{ $expedition->start_at->translatedFormat('j. n. Y') }}</time>@if($expedition->end_at)–<time datetime="{{ $expedition->end_at->toDateString() }}">{{ $expedition->end_at->translatedFormat('j. n. Y') }}</time>@endif</p>@endif
            <p>{{ $expedition->short_description }}</p>
            @if($expedition->acceptsRegistrations())<p><strong>{{ $expedition->availablePlaces() ?? 'Kapacita bude upřesněna' }} volných míst</strong></p>@endif
            <p><a class="text-link dark-link" href="{{ route('expeditions.show', $expedition) }}">Podrobnosti o expedici <span aria-hidden="true">→</span></a></p>
        </article>
    @empty
        <div class="empty-state dark-empty"><h2>Další expedici připravujeme</h2><p>Jakmile zveřejníme termín a program, najdete je tady.</p></div>
    @endforelse
</div></section>
@endsection
