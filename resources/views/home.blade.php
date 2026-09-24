@extends('layouts.app')

@section('content')
<section class="hero" aria-labelledby="hero-title">
    <div class="shell hero-grid">
        <div class="hero-copy">
            <p class="eyebrow">Přátelství · cestování · přístupnost</p>
            <h1 id="hero-title">Některé věci člověk vidí, i když je nevidí.</h1>
            <p class="lead">Čtyři kamarádi tvoří Slepé Slunce. Jeden z nás je nevidomý, ostatní pomáhají z lásky a protože spolu chceme zažívat věci, které dávají smysl. Pořádáme expedice, sdílíme zkušenosti a otevíráme cestování dalším lidem s handicapem.</p>
            <div class="button-row">
                <a class="button button-primary" href="{{ route('expeditions.index') }}">Prohlédnout expedice</a>
                <a class="button button-quiet" href="{{ route('posts.index') }}">Číst články</a>
            </div>
        </div>
        <div class="eclipse" aria-hidden="true"><span class="eclipse-orbit"></span><span class="eclipse-sun"></span><span class="eclipse-moon"></span></div>
    </div>
</section>

<section id="expedice" class="section dark-section" aria-labelledby="expedice-title">
    <div class="shell split">
        <div><p class="eyebrow">Slepé Slunce</p><h2 id="expedice-title">Nebereme nikoho „s sebou“. Cestujeme spolu.</h2></div>
        <div class="prose-intro"><p>Mirek je plnohodnotný člen expedic a spoluautor projektu. Víme, že dobrá asistence není opakem samostatnosti a že o omezeních se dá mluvit normálně, prakticky a s humorem.</p><p>Slepé Slunce je zastřešující značka pro expedice, zkušenosti a příběhy lidí, kterým handicap nemá rozhodovat o jejich plánech.</p></div>
    </div>
    @if($expeditions->isNotEmpty())
        <div class="shell expedition-cards">
            @foreach($expeditions as $item)
                <article class="expedition-card">
                    <p class="status-pill status-pill--{{ $item->status()->value }}">{{ $item->status()->label() }}</p>
                    <h3><a href="{{ route('expeditions.show', $item) }}">{{ $item->name }}</a></h3>
                    @if($item->start_at)<p><time datetime="{{ $item->start_at->toDateString() }}">{{ $item->start_at->translatedFormat('j. n. Y') }}</time>@if($item->end_at)–<time datetime="{{ $item->end_at->toDateString() }}">{{ $item->end_at->translatedFormat('j. n. Y') }}</time>@endif</p>@endif
                    <p>{{ $item->short_description }}</p>
                </article>
            @endforeach
        </div>
    @endif
</section>

<section id="smysl" class="section light-section" aria-labelledby="smysl-title">
    <div class="shell">
        <p class="eyebrow ink">Proč jedeme</p><h2 id="smysl-title" class="wide-title">Nechceme dokazovat, že handicap neexistuje.</h2>
        <div class="purpose-grid">
            <article><span aria-hidden="true">01</span><h3>Report z cesty</h3><p>Pravdivě, průběžně a bez naleštěného hrdinství. Co se povedlo, co jsme pokazili a co nás překvapilo.</p></article>
            <article><span aria-hidden="true">02</span><h3>Zkušenosti s asistencí</h3><p>Konkrétní postupy pro letiště, dopravu a cestování. Co si objednat, na co se ptát a kde pomoc skutečně funguje.</p></article>
            <article><span aria-hidden="true">03</span><h3>Odvaha vyrazit</h3><p>Ne motivační fráze. Důkaz z praxe, že omezení lze respektovat a přesto si nenechat vzít vlastní plány.</p></article>
        </div>
    </div>
</section>

<section class="section journal-section" aria-labelledby="journal-title">
    <div class="shell">
        <div class="section-heading"><div><p class="eyebrow">Z deníku</p><h2 id="journal-title">Přípravy a cesta</h2></div><a class="text-link" href="{{ route('posts.index') }}">Všechny příspěvky <span aria-hidden="true">→</span></a></div>
        @if($posts->isEmpty())
            <div class="empty-state"><p>První zápisy právě připravujeme.</p><p>Vrátíme se sem ještě před odjezdem.</p></div>
        @else
            <div class="card-grid">@foreach($posts as $post) @include('posts._card', ['post' => $post]) @endforeach</div>
        @endif
    </div>
</section>

<section id="odber" class="section light-section" aria-labelledby="odber-title">
    <div class="shell narrow-section">
        <p class="eyebrow ink">Novinky bez zahlcení</p>
        <h2 id="odber-title">Vyberte si, co chcete sledovat</h2>
        <p>Týdenní souhrny posíláme jen tehdy, když je co říct. Naléhavé aktuality mohou přijít v denním přehledu. Přihlášení dokončíte potvrzením v e-mailu.</p>
        @include('subscriptions.form', ['expeditions' => $expeditions])
    </div>
</section>
@endsection
