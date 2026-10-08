@extends('layouts.app')
@section('title', app()->isLocale('en') ? 'Accessible travel guides — Blind Sun' : 'Návody pro cestu bez bariér — Slepé Slunce')
@section('description', app()->isLocale('en') ? 'Practical guides to accessible travel by train, bus and plane, or with a companion.' : 'Praktické návody pro cestování vlakem, autobusem, letadlem i s parťákem.')

@section('content')
<header class="page-header guides-hero">
    <div class="shell">
        <p class="eyebrow">{{ app()->isLocale('en') ? 'Travel made practical' : 'Prakticky na cestách' }}</p>
        <h1>{{ app()->isLocale('en') ? 'Accessible travel guides' : 'Návody pro cestu bez bariér' }}</h1>
        <p>{{ app()->isLocale('en') ? 'Choose the situation that fits your journey. Find practical advice on transport, arranging assistance and travelling with a companion.' : 'Vyberte si podle situace. Najdete tu informace k dopravě, objednání asistence i společnému cestování s parťákem.' }}</p>
    </div>
</header>

<section class="section light-section guides-page">
    <div class="shell">
        @foreach($guideTopics as $topic => $label)
            @php($topicPosts = $posts->where('guide_topic', $topic))
            @if($topicPosts->isNotEmpty())
                <section class="guide-topic" aria-labelledby="guide-topic-{{ $topic }}">
                    <header class="guide-topic-heading">
                        <p class="guide-topic-kicker">{{ app()->isLocale('en') ? 'Topic' : 'Téma' }}</p>
                        <h2 id="guide-topic-{{ $topic }}">{{ app()->isLocale('en') ? (['pred-cestou' => 'Before you go', 'doprava' => 'Transport', 's-partakem' => 'With a companion'][$topic] ?? $label) : $label }}</h2>
                    </header>
                    <div class="guide-list">
                        @foreach($topicPosts as $post)
                            <article class="guide-list-item">
                                <div>
                                    <h3><a href="{{ route('guides.show', $post) }}">{{ $post->title }}</a></h3>
                                    <p>{{ $post->excerpt }}</p>
                                </div>
                                <a class="guide-read-link" href="{{ route('guides.show', $post) }}">{{ app()->isLocale('en') ? 'Read the guide' : 'Otevřít návod' }} <span aria-hidden="true">→</span></a>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif
        @endforeach

        @include('guides._assistance-cta')
    </div>
</section>
@endsection
