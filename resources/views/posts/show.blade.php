@extends('layouts.app')
@section('title', ($post->seo_title ?: $post->title).' — Slepé Slunce')
@section('description', $post->seo_description ?: $post->excerpt)
@section('og_type', 'article')
@if($post->cover_image) @section('og_image', url(Storage::url($post->cover_image))) @endif

@section('content')
@php
    $thumbnails = app(\App\Services\ImageThumbnail::class);
    $photoCount = $post->photoCount();
    $videoCount = $post->videoCount();
@endphp
@if($preview)<div class="preview-bar" role="status">Toto je neveřejný náhled příspěvku.</div>@endif
<article>
    <header class="article-header">
        <div class="article-shell">
            <a class="back-link" href="{{ route('posts.index') }}"><span aria-hidden="true">←</span> Deník expedice</a>
            <p class="article-meta">@if($post->journalDate())<time datetime="{{ $post->journalDateKey() }}">{{ $post->journalDate()->translatedFormat('j. F Y') }}</time>@endif @if($post->location)<span>·</span> {{ $post->location }}@endif</p>
            <h1>{{ $post->title }}</h1>
            <p class="article-lead">{{ $post->excerpt }}</p>
            @if($post->authors->isNotEmpty())<p class="byline">Napsali {{ $post->authors->pluck('name')->join(', ', ' a ') }} · {{ $post->readingMinutes() }} min čtení</p>@endif
            @if($photoCount || $videoCount)
            <nav class="article-media-summary" aria-label="Média v článku">
                @if($photoCount)<a href="#fotografie"><span aria-hidden="true">▧</span> {{ $post->photoCountLabel() }}</a>@endif
                @if($videoCount)<a href="#videa"><span aria-hidden="true">▶</span> {{ $post->videoCountLabel() }}</a>@endif
            </nav>
            @endif
        </div>
    </header>

    @if($post->cover_image)<figure class="cover-figure"><a class="full-image-link" href="{{ $thumbnails->originalUrl($post->cover_image) }}" data-full-image data-alt="{{ $post->cover_alt }}"><img src="{{ $thumbnails->url($post->cover_image, 'medium') }}" alt="{{ $post->cover_alt }}" width="1440" height="1080"><span>Zobrazit v plné velikosti</span></a></figure>@endif

    <div class="article-shell article-body">{!! $post->body !!}</div>

    @if($photoCount)
    <section id="fotografie" class="article-shell article-gallery anchored-section" aria-labelledby="gallery-title"><h2 id="gallery-title">Fotografie z cesty <small>{{ $post->photoCountLabel() }}</small></h2><div class="gallery-grid">
        @foreach($post->galleryPhotos() as $photo)<figure><a class="full-image-link" href="{{ $thumbnails->originalUrl($photo['path']) }}" data-full-image data-alt="{{ $photo['alt'] ?? '' }}" data-caption="{{ $photo['caption'] ?? '' }}"><img src="{{ $thumbnails->url($photo['path'], 'medium') }}" alt="{{ $photo['alt'] ?? '' }}" loading="lazy" width="1440" height="1080"><span>Zobrazit v plné velikosti</span></a>@if(!empty($photo['caption']))<figcaption>{{ $photo['caption'] }}</figcaption>@endif</figure>@endforeach
    </div></section>
    @endif

    @if($videoCount)
    <section id="videa" class="article-shell video-section anchored-section" aria-labelledby="videos-title"><h2 id="videos-title">Videa <small>{{ $post->videoCountLabel() }}</small></h2>
        @foreach($post->videoItems() as $video)
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

@if($post->cover_image || $photoCount)
<dialog class="image-lightbox" id="image-lightbox" aria-labelledby="image-lightbox-caption">
    <button type="button" class="image-lightbox-close" aria-label="Zavřít fotografii">×</button>
    <figure><img src="" alt=""><figcaption id="image-lightbox-caption"></figcaption></figure>
</dialog>
<script>
document.addEventListener('DOMContentLoaded',()=>{const dialog=document.getElementById('image-lightbox');if(!dialog||typeof dialog.showModal!=='function')return;const image=dialog.querySelector('img'),caption=dialog.querySelector('figcaption'),close=dialog.querySelector('.image-lightbox-close');document.querySelectorAll('[data-full-image]').forEach(link=>link.addEventListener('click',event=>{event.preventDefault();image.src=link.href;image.alt=link.dataset.alt||'';caption.textContent=link.dataset.caption||link.dataset.alt||'';dialog.showModal();close.focus()}));close.addEventListener('click',()=>dialog.close());dialog.addEventListener('click',event=>{if(event.target===dialog)dialog.close()});dialog.addEventListener('close',()=>{image.removeAttribute('src')})});
</script>
@endif
@endsection
