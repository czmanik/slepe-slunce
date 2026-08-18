@extends('layouts.app')
@section('title', 'Deník expedice — Slepé Slunce')
@section('description', 'Přípravy, cesta Španělskem, zatmění a praktické zkušenosti s asistencí pro lidi se zrakovým postižením.')

@section('content')
<header class="page-header"><div class="shell"><p class="eyebrow">Slepé Slunce</p><h1>Deník expedice</h1><p>Od prvního nápadu přes cestu Španělskem až po chvíli, kdy zhasne Slunce.</p>
@auth
<nav class="journal-actions" aria-label="Rychlé zápisy z cesty">
    <a class="button button-primary" href="{{ route('tracking.location.create', ['from' => 'journal']) }}">Oznámit polohu</a>
    <a class="button button-quiet" href="{{ route('tracking.photo.create', ['from' => 'journal']) }}">Přidat fotku na mapu</a>
</nav>
@endauth
</div></header>
<section class="section light-section">
    <div class="shell">
        @if(session('message'))<div class="journal-message" role="status">{{ session('message') }}</div>@endif
        @if($days->isNotEmpty())
        <nav class="journal-timeline" aria-label="Den expedice">
            <a href="{{ route('posts.index') }}" @if(!$selectedDay) aria-current="page" @endif>Vše</a>
            @foreach($days as $day)
                <a href="{{ route('posts.index', ['day' => $day]) }}" @if($selectedDay === $day) aria-current="page" @endif>
                    <time datetime="{{ $day }}">{{ \Illuminate\Support\Carbon::parse($day)->translatedFormat('j. F') }}</time>
                </a>
            @endforeach
        </nav>
        @endif
        @if($posts->isEmpty())<div class="empty-state dark-empty"><h2>První zápisy připravujeme</h2><p>Brzy tady najdete přípravy cesty i praktické zkušenosti s asistencí.</p></div>
        @else
            <div class="journal-days">
                @foreach($posts->groupBy(fn ($post) => $post->journalDateKey()) as $day => $dayPosts)
                <section class="journal-day" aria-labelledby="journal-day-{{ $day }}">
                    <header class="journal-day-heading">
                        <time id="journal-day-{{ $day }}" datetime="{{ $day }}">{{ \Illuminate\Support\Carbon::parse($day)->translatedFormat('l j. F Y') }}</time>
                        <span>{{ $dayPosts->count() }} {{ $dayPosts->count() === 1 ? 'zápis' : ($dayPosts->count() < 5 ? 'zápisy' : 'zápisů') }}</span>
                    </header>
                    <div class="card-grid">@foreach($dayPosts as $post) @include('posts._card', ['post' => $post]) @endforeach</div>
                </section>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endsection
