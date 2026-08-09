@extends('layouts.app')
@section('title', ($post->seo_title ?: $post->title).' — Slepé Slunce')
@section('description', $post->seo_description ?: $post->excerpt)
@section('og_type', 'article')
@if($post->cover_image) @section('og_image', url(Storage::url($post->cover_image))) @endif

@section('content')
@if($preview)<div class="preview-bar" role="status">Toto je neveřejný náhled příspěvku.</div>@endif
<article>
    <header class="article-header">
        <div class="article-shell">
            <a class="back-link" href="{{ route('posts.index') }}"><span aria-hidden="true">←</span> Deník expedice</a>
            <p class="article-meta">@if($post->published_at)<time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->translatedFormat('j. F Y') }}</time>@endif @if($post->location)<span>·</span> {{ $post->location }}@endif</p>
            <h1>{{ $post->title }}</h1>
            <p class="article-lead">{{ $post->excerpt }}</p>
            @if($post->authors->isNotEmpty())<p class="byline">Napsali {{ $post->authors->pluck('name')->join(', ', ' a ') }} · {{ $post->readingMinutes() }} min čtení</p>@endif
        </div>
    </header>

    @if($post->cover_image)<figure class="cover-figure"><img src="{{ Storage::url($post->cover_image) }}" alt="{{ $post->cover_alt }}" width="1600" height="1000"></figure>@endif

    <div class="article-shell article-body">{!! $post->body !!}</div>

    @if(!empty($post->gallery))
    <section class="article-shell article-gallery" aria-labelledby="gallery-title"><h2 id="gallery-title">Fotografie z cesty</h2><div class="gallery-grid">
        @foreach($post->gallery as $photo)<figure><img src="{{ Storage::url($photo['path']) }}" alt="{{ $photo['alt'] }}" loading="lazy" width="1000" height="750">@if(!empty($photo['caption']))<figcaption>{{ $photo['caption'] }}</figcaption>@endif</figure>@endforeach
    </div></section>
    @endif

    @if(!empty($post->videos))
    <section class="article-shell video-section" aria-labelledby="videos-title"><h2 id="videos-title">Videa</h2>
        @foreach($post->videos as $video)
            @php($youtubeId = \App\Support\YouTube::id($video['url'] ?? null))
            <article class="video-item"><h3>{{ $video['title'] }}</h3><p>{{ $video['description'] }}</p>
                @if($youtubeId)<div class="video-frame"><iframe src="https://www.youtube-nocookie.com/embed/{{ $youtubeId }}" title="{{ $video['title'] }}" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe></div>@endif
                <p><a class="text-link ink-link" href="{{ $video['url'] }}">Přehrát video na YouTube</a></p>
                @if(!empty($video['transcript']))<details><summary>Přepis videa</summary><div class="transcript">{{ $video['transcript'] }}</div></details>@endif
            </article>
        @endforeach
    </section>
    @endif
</article>
@endsection
