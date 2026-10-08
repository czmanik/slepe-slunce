@extends('layouts.app')
@section('title', app()->isLocale('en') ? (isset($expedition) ? 'Journal — '.$expedition->name.' — Blind Sun' : 'Journal — Blind Sun') : ($category === \App\Models\Post::CATEGORY_TRAVEL ? 'Cestování bez bariér — Slepé Slunce' : (isset($expedition) ? 'Deník — '.$expedition->name : 'Deník — Slepé Slunce')))
@section('description', app()->isLocale('en') ? 'Stories, experiences and practical lessons from our accessible journeys.' : ($category === \App\Models\Post::CATEGORY_TRAVEL ? 'Praktické zkušenosti, nároky a návody pro asistované cestování nevidomých lidí.' : 'Příběhy našich expedic, setkání a zkušenosti z cest.'))

@section('content')
@php($journalRoute = isset($expedition) ? 'expeditions.posts' : 'posts.index')
@php($isTravelCategory = $category === \App\Models\Post::CATEGORY_TRAVEL)
@php($isEnglish = app()->isLocale('en'))
<header class="page-header journal-hero">
    <div class="shell">
        <p class="eyebrow">{{ $isEnglish ? ($isTravelCategory ? 'Travel made practical' : 'Stories from the road') : ($isTravelCategory ? 'Prakticky na cestách' : 'Příběhy z cest') }}</p>
        <h1>{{ $isEnglish ? ($isTravelCategory ? 'Accessible travel' : 'Blind Sun journal') : ($isTravelCategory ? 'Cestování bez bariér' : 'Deník Slepého slunce') }}</h1>
        <p>{{ $isEnglish ? ($isTravelCategory ? 'Practical guides and first-hand experiences of accessible travel.' : (isset($expedition) ? 'Stories from the '.$expedition->name.' expedition, from the beginning.' : 'Journeys, encounters and moments worth sharing.')) : ($isTravelCategory ? 'Praktické návody, práva cestujících a zkušenosti s asistovaným cestováním.' : (isset($expedition) ? 'Zápisy z expedice '.$expedition->name.'. Čtěte náš společný příběh od začátku.' : 'Cesty, setkání a chvíle, které stojí za zaznamenání.')) }}</p>
    </div>
</header>
<section class="section light-section journal-page">
    <div class="shell">
        @if(session('message'))<div class="journal-message" role="status">{{ session('message') }}</div>@endif
        @unless($isTravelCategory)
        <div class="journal-filter-panel">
            <p class="journal-kicker">{{ $isEnglish ? 'Choose a journey' : 'Vyberte si příběh' }}</p>
            <nav class="journal-expedition-switcher" aria-label="{{ $isEnglish ? 'Filter the journal by expedition' : 'Filtrovat deník podle expedice' }}">
                <a href="{{ route('posts.index') }}" @if(!isset($expedition)) aria-current="page" @endif>{{ $isEnglish ? 'All stories' : 'Všechny příběhy' }}</a>
                @foreach($expeditions as $journalExpedition)
                    <a href="{{ route('expeditions.posts', $journalExpedition) }}" @if(isset($expedition) && $expedition->is($journalExpedition)) aria-current="page" @endif>
                        <span>{{ $journalExpedition->name }}</span>
                        <small>{{ $journalExpedition->posts_count }} {{ $isEnglish ? ($journalExpedition->posts_count === 1 ? 'entry' : 'entries') : ($journalExpedition->posts_count === 1 ? 'zápis' : ($journalExpedition->posts_count < 5 ? 'zápisy' : 'zápisů')) }}</small>
                    </a>
                @endforeach
            </nav>
            @if(isset($expedition) && $days->isNotEmpty())
                <div class="journal-day-filter">
                    <p class="journal-kicker">{{ $isEnglish ? 'Jump to a day' : 'Přejít na den' }}</p>
                    <nav class="journal-timeline" aria-label="{{ $isEnglish ? 'Filter by day' : 'Filtrovat podle dne' }}">
                        <a href="{{ route($journalRoute, [$expedition]) }}" @if(!$selectedDay) aria-current="page" @endif>{{ $isEnglish ? 'Whole journey' : 'Celá cesta' }}</a>
                        @foreach($days as $day)
                            <a href="{{ route($journalRoute, [$expedition, 'day' => $day]) }}" @if($selectedDay === $day) aria-current="page" @endif><time datetime="{{ $day }}">{{ \Illuminate\Support\Carbon::parse($day)->translatedFormat('j. F Y') }}</time></a>
                        @endforeach
                    </nav>
                </div>
            @endif
        </div>
        @endunless

        @if($posts->isEmpty())
            <div class="empty-state dark-empty"><h2>{{ $isEnglish ? 'Stories are coming soon' : ($isTravelCategory ? 'První články připravujeme' : 'První zápisy připravujeme') }}</h2><p>{{ $isEnglish ? 'Come back soon for practical advice and stories from the road.' : ($isTravelCategory ? 'Brzy tu najdete praktické rady pro asistované cestování.' : 'Brzy tu najdete příběhy z cesty.') }}</p></div>
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
                        <p class="journal-kicker">{{ $isEnglish ? 'Latest entry' : 'Nejnovější zápis' }} @if($featuredPost->expedition) · {{ $featuredPost->expedition->name }} @endif</p>
                        <h2 id="journal-featured-title"><a href="{{ route($isTravelCategory ? 'guides.show' : 'posts.show', $featuredPost) }}">{{ $featuredPost->title }}</a></h2>
                        <p class="journal-featured-date"><time datetime="{{ $featuredPost->journalDateKey() }}">{{ $featuredPost->journalDate()?->translatedFormat('j. F Y') }}</time></p>
                        @if($featuredPost->excerpt)<p>{{ $featuredPost->excerpt }}</p>@endif
                        <a class="journal-read-link" href="{{ route($isTravelCategory ? 'guides.show' : 'posts.show', $featuredPost) }}">{{ $isEnglish ? 'Read the story' : 'Přečíst zápis' }} <span aria-hidden="true">→</span></a>
                    </div>
                </section>
                @php($listingPosts = $posts->skip(1))
            @else
                @php($listingPosts = $posts)
            @endif

            @if($listingPosts->isNotEmpty())
                <div class="journal-list-heading">
                    <h2>{{ $isEnglish ? ($isTravelCategory ? 'More guides' : (isset($expedition) ? 'Journey entries' : 'More stories')) : ($isTravelCategory ? 'Další články' : (isset($expedition) ? 'Zápisy z cesty' : 'Další zápisy')) }}</h2>
                    <p>{{ $isEnglish ? ($isTravelCategory ? 'Practical advice for your journey' : (isset($expedition) ? 'From the first entry to the last' : 'The latest stories first')) : ($isTravelCategory ? 'Praktické informace pro cestu' : (isset($expedition) ? 'Od prvního zápisu po poslední' : 'Od nejnovějších příběhů')) }}</p>
                </div>
                <div class="journal-days">
                    @foreach($listingPosts->groupBy(fn ($post) => $post->journalDateKey()) as $day => $dayPosts)
                        <section class="journal-day" aria-labelledby="journal-day-{{ $day }}">
                            <header class="journal-day-heading">
                                <time id="journal-day-{{ $day }}" datetime="{{ $day }}">{{ \Illuminate\Support\Carbon::parse($day)->translatedFormat('l j. F Y') }}</time>
                                <span>{{ $dayPosts->count() }} {{ $isEnglish ? ($dayPosts->count() === 1 ? 'entry' : 'entries') : ($dayPosts->count() === 1 ? 'zápis' : ($dayPosts->count() < 5 ? 'zápisy' : 'zápisů')) }}</span>
                            </header>
                            <div class="journal-entry-list">
                                @foreach($dayPosts as $post)
                                    <article class="journal-entry">
                                        <a class="journal-entry-image" href="{{ route($isTravelCategory ? 'guides.show' : 'posts.show', $post) }}" tabindex="-1" aria-hidden="true">
                                            @if($post->cover_image)<img src="{{ app(\App\Services\ImageThumbnail::class)->url($post->cover_image, 'small') }}" alt="" loading="lazy" width="480" height="320">@else<span class="card-placeholder"></span>@endif
                                        </a>
                                        <div class="journal-entry-copy">
                                            @if(!isset($expedition) && $post->expedition)<p class="journal-kicker">{{ $post->expedition->name }}</p>@endif
                                            <h3><a href="{{ route($isTravelCategory ? 'guides.show' : 'posts.show', $post) }}">{{ $post->title }}</a></h3>
                                            @if($post->excerpt)<p>{{ $post->excerpt }}</p>@endif
                                            @if($post->location)<p class="journal-entry-location">{{ $post->location }}</p>@endif
                                            <a class="journal-read-link" href="{{ route($isTravelCategory ? 'guides.show' : 'posts.show', $post) }}">{{ $isEnglish ? 'Read the story' : 'Přečíst zápis' }} <span aria-hidden="true">→</span></a>
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
            <aside class="journal-editor-tools" aria-label="{{ $isEnglish ? 'Expedition member tools' : 'Nástroje pro členy expedice' }}">
                <h2>{{ $isEnglish ? 'Share your journey' : 'Zápisy z cesty' }}</h2>
                <p>{{ $isEnglish ? 'Quick tools for signed-in members.' : 'Rychlé nástroje pro přihlášené členy.' }}</p>
                <div class="journal-actions">
                    <a class="button button-primary" href="{{ route('tracking.location.create', ['from' => 'journal', 'expedition_id' => $expedition?->id]) }}">{{ $isEnglish ? 'Share location' : 'Oznámit polohu' }}</a>
                    <a class="button button-quiet" href="{{ route('tracking.photo.create', ['from' => 'journal', 'expedition_id' => $expedition?->id]) }}">{{ $isEnglish ? 'Add a photo to the map' : 'Přidat fotku na mapu' }}</a>
                </div>
            </aside>
        @endauth
    </div>
</section>
@endsection
