@extends('layouts.app')

@section('content')
<section class="hero" aria-labelledby="hero-title">
    <div class="shell hero-grid">
        <div class="hero-copy">
            <p class="eyebrow">Španělsko · srpen 2026</p>
            <h1 id="hero-title">Některé věci člověk vidí, i když je nevidí.</h1>
            <p class="lead">Čtyři kamarádi jedou přes Španělsko za úplným zatměním Slunce. Jeden z nás téměř nevidí. Budeme vyprávět, jak se cestuje, když překážky berete vážně — ale nenecháte je rozhodovat.</p>
            <div class="button-row">
                <a class="button button-primary" href="{{ route('posts.index') }}">Číst deník expedice</a>
                <a class="button button-quiet" href="#smysl">Proč to děláme</a>
            </div>
        </div>
        <div class="eclipse" aria-hidden="true"><span class="eclipse-orbit"></span><span class="eclipse-sun"></span><span class="eclipse-moon"></span></div>
    </div>
</section>

<section id="expedice" class="section dark-section" aria-labelledby="expedice-title">
    <div class="shell split">
        <div><p class="eyebrow">Slepé Slunce</p><h2 id="expedice-title">Není to výlet, na kterém někoho vezeme s sebou.</h2></div>
        <div class="prose-intro"><p>Mirek je plnohodnotný člen expedice a spoluautor projektu. Už jsme spolu něco procestovali. Víme tedy, že dobrá asistence není opakem samostatnosti a že o omezeních se dá mluvit normálně, prakticky a s humorem.</p><p>Jedeme čtyři. Letadlem do Barcelony, autem přes Španělsko a nakonec pod oblohu, která na několik minut zhasne.</p></div>
    </div>
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
@endsection
