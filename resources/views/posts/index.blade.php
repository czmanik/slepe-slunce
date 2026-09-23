@extends('layouts.app')
@section('title', isset($expedition) ? 'Deník — '.$expedition->name : 'Deník — Slepé Slunce')
@section('description', 'Příběhy našich expedic, setkání a zkušenosti z cest.')

@section('content')
@php($journalRoute = isset($expedition) ? 'expeditions.posts' : 'posts.index')
<header class="page-header journal-hero">
    <div class="shell">
        <p class="eyebrow">Příběhy z cest</p>
        <h1>Deník Slepého slunce</h1>
        <p>{{ isset($expedition) ? 'Zápisy z expedice '.$expedition->name.'. Čtěte náš společný příběh od začátku.' : 'Cesty, setkání a chvíle, které stojí za zaznamenání.' }}</p>
    </div>
</header>
<section class="section light-section journal-page">
    <div class="shell">
        @if(session('message'))<div class="journal-message" role="status">{{ session('message') }}</div>@endif
        <div class="journal-filter-panel">
            <p class="journal-kicker">Vyberte si příběh</p>
            <nav class="journal-expedition-switcher" aria-label="Filtrovat deník podle expedice">
                <a href="{{ route('posts.index') }}" @if(!isset($expedition)) aria-current="page" @endif>Všechny příběhy</a>
                @foreach($expeditions as $journalExpedition)
                    <a href="{{ route('expeditions.posts', $journalExpedition) }}" @if(isset($expedition) && $expedition->is($journalExpedition)) aria-current="page" @endif>
                        <span>{{ $journalExpedition->name }}</span>
                        <small>{{ $journalExpedition->posts_count }} {{ $journalExpedition->posts_count === 1 ? 'zápis' : ($journalExpedition->posts_count < 5 ? 'zápisy' : 'zápisů') }}</small>
                    </a>
                @endforeach
            </nav>
            @if(isset($expedition) && $days->isNotEmpty())
                <div class="journal-day-filter">
                    <p class="journal-kicker">Přejít na den</p>
                    <nav class="journal-timeline" aria-label="Filtrovat podle dne">
                        <a href="{{ route($journalRoute, [$expedition]) }}" @if(!$selectedDay) aria-current="page" @endif>Celá cesta</a>
                        @foreach($days as $day)
                            <a href="{{ route($journalRoute, [$expedition, 'day' => $day]) }}" @if($selectedDay === $day) aria-current="page" @endif><time datetime="{{ $day }}">{{ \Illuminate\Support\Carbon::parse($day)->translatedFormat('j. F Y') }}</time></a>
                        @endforeach
                    </nav>
                </div>
            @endif
        </div>

        @if($posts->isEmpty())
            <div class="empty-state dark-empty"><h2>První zápisy připravujeme</h2><p>Brzy tu najdete příběhy z cesty.</p></div>
        @else
            @if(!isset($expedition) && !$selectedDay)
                @php($featuredPost = $posts->first())
                <section class="journal-featured" aria-labelledby="journal-featured-title">
                    <div class="journal-featured-media">
                        @if($featuredPost->cover_image)
                            <img src="{{ app(\App\Services\ImageThumbnail::class)->url($featuredPost->cover_image, 'medium') }}" alt="{{ $featuredPost->cover_alt ?: '' }}" width="960" height="640">
                        @else
                            <div class="journal-featured-placeholder" aria-hidden="true"></div>
                        @endif
                    </div>
                    <div class="journal-featured-copy">
                        <p class="journal-kicker">Nejnovější zápis @if($featuredPost->expedition) · {{ $featuredPost->expedition->name }} @endif</p>
                        <h2 id="journal-featured-title"><a href="{{ route('posts.show', $featuredPost) }}">{{ $featuredPost->title }}</a></h2>
                        <p class="journal-featured-date"><time datetime="{{ $featuredPost->journalDateKey() }}">{{ $featuredPost->journalDate()?->translatedFormat('j. F Y') }}</time></p>
                        @if($featuredPost->excerpt)<p>{{ $featuredPost->excerpt }}</p>@endif
                        <a class="journal-read-link" href="{{ route('posts.show', $featuredPost) }}">Přečíst zápis <span aria-hidden="true">→</span></a>
                    </div>
                </section>
                @php($listingPosts = $posts->skip(1))
            @else
                @php($listingPosts = $posts)
            @endif

            @if($listingPosts->isNotEmpty())
                <div class="journal-list-heading">
                    <h2>{{ isset($expedition) ? 'Zápisy z cesty' : 'Další zápisy' }}</h2>
                    <p>{{ isset($expedition) ? 'Od prvního zápisu po poslední' : 'Od nejnovějších příběhů' }}</p>
                </div>
                <div class="journal-days">
                    @foreach($listingPosts->groupBy(fn ($post) => $post->journalDateKey()) as $day => $dayPosts)
                        <section class="journal-day" aria-labelledby="journal-day-{{ $day }}">
                            <header class="journal-day-heading">
                                <time id="journal-day-{{ $day }}" datetime="{{ $day }}">{{ \Illuminate\Support\Carbon::parse($day)->translatedFormat('l j. F Y') }}</time>
                                <span>{{ $dayPosts->count() }} {{ $dayPosts->count() === 1 ? 'zápis' : ($dayPosts->count() < 5 ? 'zápisy' : 'zápisů') }}</span>
                            </header>
                            <div class="journal-entry-list">
                                @foreach($dayPosts as $post)
                                    <article class="journal-entry">
                                        <a class="journal-entry-image" href="{{ route('posts.show', $post) }}" tabindex="-1" aria-hidden="true">
                                            @if($post->cover_image)<img src="{{ app(\App\Services\ImageThumbnail::class)->url($post->cover_image, 'small') }}" alt="" loading="lazy" width="480" height="320">@else<span class="card-placeholder"></span>@endif
                                        </a>
                                        <div class="journal-entry-copy">
                                            @if(!isset($expedition) && $post->expedition)<p class="journal-kicker">{{ $post->expedition->name }}</p>@endif
                                            <h3><a href="{{ route('posts.show', $post) }}">{{ $post->title }}</a></h3>
                                            @if($post->excerpt)<p>{{ $post->excerpt }}</p>@endif
                                            @if($post->location)<p class="journal-entry-location">{{ $post->location }}</p>@endif
                                            <a class="journal-read-link" href="{{ route('posts.show', $post) }}">Přečíst zápis <span aria-hidden="true">→</span></a>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        </section>
                    @endforeach
                </div>
            @endif
        @endif
        @auth
            <aside class="journal-editor-tools" aria-label="Nástroje pro členy expedice">
                <h2>Zápisy z cesty</h2>
                <p>Rychlé nástroje pro přihlášené členy.</p>
                <div class="journal-actions">
                    <a class="button button-primary" href="{{ route('tracking.location.create', ['from' => 'journal']) }}">Oznámit polohu</a>
                    <a class="button button-quiet" href="{{ route('tracking.photo.create', ['from' => 'journal']) }}">Přidat fotku na mapu</a>
                </div>
            </aside>
        @endauth
    </div>
</section>
@endsection
