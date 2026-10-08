@extends('layouts.app')

@section('content')
@php($isEnglish = $isEnglishSite ?? app()->isLocale('en'))
<section class="hero" aria-labelledby="hero-title">
    <div class="shell hero-grid">
        <div class="hero-copy">
            <p class="eyebrow">{{ $isEnglish ? 'Friendship · travel · accessibility' : 'Přátelství · cestování · přístupnost' }}</p>
            <h1 id="hero-title">{{ $isEnglish ? 'Some things can be seen even when you cannot see them.' : 'Některé věci člověk vidí, i když je nevidí.' }}</h1>
            <p class="lead">{{ $isEnglish ? 'We are a Czech initiative currently based in Estepona, Spain. Four friends make up Blind Sun. One of us is blind; the others help out of friendship and because we want to share experiences that matter. We organise expeditions, share what we learn and open travel to more people with disabilities.' : 'Jsme česká iniciativa se současnou základnou v Esteponě ve Španělsku. Čtyři kamarádi tvoří Slepé Slunce. Jeden z nás je nevidomý, ostatní pomáhají z lásky a protože spolu chceme zažívat věci, které dávají smysl. Pořádáme expedice, sdílíme zkušenosti a otevíráme cestování dalším lidem s handicapem.' }}</p>
            <div class="button-row">
                <a class="button button-primary" href="{{ route('expeditions.index') }}">{{ $isEnglish ? 'Explore expeditions' : 'Prohlédnout expedice' }}</a>
                <a class="button button-quiet" href="{{ route('posts.index') }}">{{ $isEnglish ? 'Read the journal' : 'Číst články' }}</a>
            </div>
        </div>
        <div class="eclipse" aria-hidden="true"><span class="eclipse-orbit"></span><span class="eclipse-sun"></span><span class="eclipse-moon"></span></div>
    </div>
</section>

<section id="expedice" class="section dark-section" aria-labelledby="expedice-title">
    <div class="shell split">
        <div><p class="eyebrow">{{ $isEnglish ? 'Blind Sun' : 'Slepé Slunce' }}</p><h2 id="expedice-title">{{ $isEnglish ? 'Nobody comes along with us. We travel together.' : 'Nebereme nikoho „s sebou“. Cestujeme spolu.' }}</h2></div>
        <div class="prose-intro">
            @if($isEnglish)
                <p>Mirek is a full member of every expedition and a co-author of the project. We know that good assistance is not the opposite of independence, and that limitations can be discussed normally, practically and with humour.</p>
                <p>Blind Sun brings together expeditions, experience and stories of people whose disability should not decide their plans.</p>
            @else
                <p>Mirek je plnohodnotný člen expedic a spoluautor projektu. Víme, že dobrá asistence není opakem samostatnosti a že o omezeních se dá mluvit normálně, prakticky a s humorem.</p>
                <p>Slepé Slunce je zastřešující značka pro expedice, zkušenosti a příběhy lidí, kterým handicap nemá rozhodovat o jejich plánech.</p>
            @endif
        </div>
    </div>
    @if($expeditions->isNotEmpty())
        <div class="shell expedition-cards">
            @foreach($expeditions as $item)
                <article class="expedition-card">
                    <p class="status-pill status-pill--{{ $item->status()->value }}">{{ $isEnglish ? ucfirst(str_replace('_', ' ', $item->status()->value)) : $item->status()->label() }}</p>
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
        <p class="eyebrow ink">{{ $isEnglish ? 'Why we travel' : 'Proč jedeme' }}</p><h2 id="smysl-title" class="wide-title">{{ $isEnglish ? 'We do not want to pretend disability does not exist.' : 'Nechceme dokazovat, že handicap neexistuje.' }}</h2>
        <div class="purpose-grid">
            <article><span aria-hidden="true">01</span><h3>{{ $isEnglish ? 'A travel report' : 'Report z cesty' }}</h3><p>{{ $isEnglish ? 'Truthfully, as we go, and without polished heroism. What worked, what we got wrong and what surprised us.' : 'Pravdivě, průběžně a bez naleštěného hrdinství. Co se povedlo, co jsme pokazili a co nás překvapilo.' }}</p></article>
            <article><span aria-hidden="true">02</span><h3>{{ $isEnglish ? 'Experience with assistance' : 'Zkušenosti s asistencí' }}</h3><p>{{ $isEnglish ? 'Useful approaches for airports, transport and travel: what to book, what to ask and where help really works.' : 'Konkrétní postupy pro letiště, dopravu a cestování. Co si objednat, na co se ptát a kde pomoc skutečně funguje.' }}</p></article>
            <article><span aria-hidden="true">03</span><h3>{{ $isEnglish ? 'The courage to set off' : 'Odvaha vyrazit' }}</h3><p>{{ $isEnglish ? 'Not motivational slogans. Evidence from real life that limitations can be respected without giving up your own plans.' : 'Ne motivační fráze. Důkaz z praxe, že omezení lze respektovat a přesto si nenechat vzít vlastní plány.' }}</p></article>
        </div>
    </div>
</section>

<section class="section journal-section" aria-labelledby="journal-title">
    <div class="shell">
        <div class="section-heading"><div><p class="eyebrow">{{ $isEnglish ? 'From the journal' : 'Z deníku' }}</p><h2 id="journal-title">{{ $isEnglish ? 'Preparations and the journey' : 'Přípravy a cesta' }}</h2></div><a class="text-link" href="{{ route('posts.index') }}">{{ $isEnglish ? 'All posts' : 'Všechny příspěvky' }} <span aria-hidden="true">→</span></a></div>
        @if($posts->isEmpty())
            <div class="empty-state"><p>{{ $isEnglish ? 'We are preparing the first posts.' : 'První zápisy právě připravujeme.' }}</p><p>{{ $isEnglish ? 'Please come back before the next trip.' : 'Vrátíme se sem ještě před odjezdem.' }}</p></div>
        @else
            <div class="card-grid">@foreach($posts as $post) @include('posts._card', ['post' => $post]) @endforeach</div>
        @endif
    </div>
</section>

@include('expeditions._latest')

<section id="odber" class="section light-section" aria-labelledby="odber-title">
    <div class="shell narrow-section">
        <p class="eyebrow ink">{{ $isEnglish ? 'Updates without the noise' : 'Novinky bez zahlcení' }}</p>
        <h2 id="odber-title">{{ $isEnglish ? 'Choose what you want to follow' : 'Vyberte si, co chcete sledovat' }}</h2>
        <p>{{ $isEnglish ? 'We send weekly summaries only when there is something to say. You complete your subscription through an email confirmation.' : 'Týdenní souhrny posíláme jen tehdy, když je co říct. Naléhavé aktuality mohou přijít v denním přehledu. Přihlášení dokončíte potvrzením v e-mailu.' }}</p>
        @include('subscriptions.form', ['expeditions' => $expeditions])
    </div>
</section>
@endsection
