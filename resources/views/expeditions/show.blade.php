@extends('layouts.app')

@section('title', $expedition->name.' — Slepé Slunce')
@section('description', $expedition->short_description ?: 'Podrobnosti expedice '.$expedition->name)

@section('content')
<header class="page-header"><div class="shell">
    <p class="eyebrow">{{ $expedition->status()->label() }}</p>
    <h1>{{ $expedition->name }}</h1>
    @if($expedition->short_description)<p>{{ $expedition->short_description }}</p>@endif
    <div class="expedition-facts">
        @if($expedition->start_at)<p><strong>Termín:</strong> <time datetime="{{ $expedition->start_at->toDateString() }}">{{ $expedition->start_at->translatedFormat('j. n. Y') }}</time>@if($expedition->end_at)–<time datetime="{{ $expedition->end_at->toDateString() }}">{{ $expedition->end_at->translatedFormat('j. n. Y') }}</time>@endif</p>@endif
        @if($expedition->leader_name)<p><strong>Vedoucí expedice:</strong> {{ $expedition->leader_name }}</p>@endif
        @if($expedition->price_czk)<p><strong>Orientační cena:</strong> {{ number_format((float) $expedition->price_czk, 0, ',', ' ') }} Kč za osobu</p>@endif
        @if($expedition->acceptsRegistrations())<p><strong>Volná místa:</strong> {{ $expedition->availablePlaces() ?? 'kapacita není omezena' }}</p>@endif
    </div>
    <div class="button-row">
        <a class="button button-primary" href="{{ route('expeditions.route', $expedition) }}">Program a trasa</a>
        @if($expedition->acceptsRegistrations())<a class="button button-quiet" href="{{ route('expeditions.register', $expedition) }}">Přihlásit se</a>@endif
    </div>
</div></header>

<section class="section light-section"><div class="shell split">
    <div><p class="eyebrow ink">O expedici</p><h2>Co společně zažijeme</h2></div>
    <div class="prose-intro dark-prose"><p>{!! nl2br(e($expedition->description ?: 'Podrobný popis právě připravujeme.')) !!}</p></div>
</div></section>

@if(data_get($expedition->settings, 'prototype'))
<section class="section prototype-notice"><div class="shell narrow-section"><h2>Návrh pro testovací provoz</h2><p>Termín, partneři, cena i jednotlivé časy jsou připravené jako pracovní návrh. Rezervaci vždy osobně potvrdíme až po upřesnění všech podmínek.</p></div></section>
@endif

@if($expedition->transport_details || $expedition->accommodation_details || $expedition->accessibility_details)
<section class="section dark-section"><div class="shell info-grid">
    @if($expedition->transport_details)<article><h2>Doprava</h2><p>{{ $expedition->transport_details }}</p></article>@endif
    @if($expedition->accommodation_details)<article><h2>Ubytování</h2><p>{{ $expedition->accommodation_details }}</p></article>@endif
    @if($expedition->accessibility_details)<article><h2>Přístupnost a asistence</h2><p>{{ $expedition->accessibility_details }}</p></article>@endif
</div></section>
@endif

@if($expedition->departure_details || $expedition->included_services || $expedition->cancellation_terms)
<section class="section light-section"><div class="shell info-grid">
    @if($expedition->departure_details)<article><h2>Setkání a odjezd</h2><p>{{ $expedition->departure_details }}</p></article>@endif
    @if($expedition->included_services)<article><h2>Co je v ceně</h2><p>{{ $expedition->included_services }}</p></article>@endif
    @if($expedition->cancellation_terms)<article><h2>Rezervace a storno</h2><p>{{ $expedition->cancellation_terms }}</p></article>@endif
</div></section>
@endif

@if($expedition->posts->isNotEmpty())
<section class="section journal-section"><div class="shell"><div class="section-heading"><div><p class="eyebrow">Deník expedice</p><h2>Poslední články</h2></div><a class="text-link" href="{{ route('expeditions.posts', $expedition) }}">Celý deník</a></div><div class="card-grid">@foreach($expedition->posts as $post) @include('posts._card', ['post' => $post]) @endforeach</div></div></section>
@endif
@endsection
