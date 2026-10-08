@extends('layouts.app')
@php($isEnglish = $isEnglishSite ?? app()->isLocale('en'))

@section('title', $isEnglish ? 'Expeditions — Blind Sun' : 'Expedice — Slepé Slunce')
@section('description', $isEnglish ? 'Accessible expeditions by a Czech initiative based in Estepona, Spain.' : 'Proběhlé i připravované přístupné expedice projektu Slepé Slunce.')

@section('content')
<header class="page-header"><div class="shell"><p class="eyebrow">{{ $isEnglish ? 'We travel together' : 'Cestujeme spolu' }}</p><h1>{{ $isEnglish ? 'Expeditions' : 'Expedice' }}</h1><p>{{ $isEnglish ? 'Blind Sun is a Czech initiative based in Estepona, Spain. Every journey has its own story, team and journal, and sometimes an application form.' : 'Jsme česká iniciativa se základnou v Esteponě ve Španělsku. Každá cesta má vlastní příběh, tým, deník a podle nastavení také přihlášku.' }}</p></div></header>
<section class="section light-section"><div class="shell expedition-cards">
    @forelse($expeditions as $expedition)
        <article class="expedition-card">
            <p class="status-pill status-pill--{{ $expedition->status()->value }}">{{ $isEnglish ? ucfirst(str_replace('_', ' ', $expedition->status()->value)) : $expedition->status()->label() }}</p>
            <h2><a href="{{ route('expeditions.show', $expedition) }}">{{ $expedition->name }}</a></h2>
            @if($expedition->start_at)<p class="expedition-date"><time datetime="{{ $expedition->start_at->toDateString() }}">{{ $expedition->start_at->translatedFormat('j. n. Y') }}</time>@if($expedition->end_at)–<time datetime="{{ $expedition->end_at->toDateString() }}">{{ $expedition->end_at->translatedFormat('j. n. Y') }}</time>@endif</p>@endif
            <p>{{ $expedition->short_description }}</p>
            @if($expedition->price_czk)<p><strong>{{ $isEnglish ? 'From' : 'Orientačně' }} {{ number_format((float) $expedition->price_czk, 0, ',', ' ') }} {{ $isEnglish ? 'CZK per person' : 'Kč za osobu' }}</strong></p>@endif
            @if($expedition->acceptsRegistrations())<p><strong>{{ $expedition->availablePlaces() ?? ($isEnglish ? 'Capacity to be confirmed' : 'Kapacita bude upřesněna') }} {{ $isEnglish ? 'places available' : 'volných míst' }}</strong></p>@endif
            <p><a class="text-link dark-link" href="{{ route('expeditions.show', $expedition) }}">{{ $isEnglish ? 'Expedition details' : 'Podrobnosti o expedici' }} <span aria-hidden="true">→</span></a></p>
        </article>
    @empty
        <div class="empty-state dark-empty"><h2>{{ $isEnglish ? 'We are preparing the next expedition' : 'Další expedici připravujeme' }}</h2><p>{{ $isEnglish ? 'Its date and programme will appear here once confirmed.' : 'Jakmile zveřejníme termín a program, najdete je tady.' }}</p></div>
    @endforelse
</div></section>
@endsection
