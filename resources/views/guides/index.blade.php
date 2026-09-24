@extends('layouts.app')
@section('title', 'Návody pro cestu bez bariér — Slepé Slunce')
@section('description', 'Praktické návody pro cestování vlakem, autobusem, letadlem i s parťákem.')

@section('content')
<header class="page-header guides-hero">
    <div class="shell">
        <p class="eyebrow">Prakticky na cestách</p>
        <h1>Návody pro cestu bez bariér</h1>
        <p>Vyberte si podle situace. Najdete tu informace k dopravě, objednání asistence i společnému cestování s parťákem.</p>
    </div>
</header>

<section class="section light-section guides-page">
    <div class="shell">
        @foreach($guideTopics as $topic => $label)
            @php($topicPosts = $posts->where('guide_topic', $topic))
            @if($topicPosts->isNotEmpty())
                <section class="guide-topic" aria-labelledby="guide-topic-{{ $topic }}">
                    <header class="guide-topic-heading">
                        <p class="guide-topic-kicker">Téma</p>
                        <h2 id="guide-topic-{{ $topic }}">{{ $label }}</h2>
                    </header>
                    <div class="guide-list">
                        @foreach($topicPosts as $post)
                            <article class="guide-list-item">
                                <div>
                                    <h3><a href="{{ route('guides.show', $post) }}">{{ $post->title }}</a></h3>
                                    <p>{{ $post->excerpt }}</p>
                                </div>
                                <a class="guide-read-link" href="{{ route('guides.show', $post) }}">Otevřít návod <span aria-hidden="true">→</span></a>
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
