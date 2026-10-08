@extends('layouts.app')
@section('title', ($post->seo_title ?: $post->title).' — '.(app()->isLocale('en') ? 'Blind Sun' : 'Slepé Slunce'))
@section('description', $post->seo_description ?: $post->excerpt)
@section('og_type', 'article')
@if($post->cover_image) @section('og_image', url(Storage::url($post->cover_image))) @endif

@section('content')
@php
    $thumbnails = app(\App\Services\ImageThumbnail::class);
    $photoCount = $post->photoCount();
    $videoCount = $post->videoCount();
    $isEnglish = app()->isLocale('en');
@endphp
@if($preview)<div class="preview-bar" role="status">{{ $isEnglish ? 'This is a private preview of the post.' : 'Toto je neveřejný náhled příspěvku.' }}</div>@endif
<article>
    <header class="article-header">
        <div class="article-shell">
            <a class="back-link" href="{{ $post->category === \App\Models\Post::CATEGORY_TRAVEL ? route('guides.index') : ($post->expedition ? route('expeditions.posts', $post->expedition) : route('posts.index')) }}"><span aria-hidden="true">←</span> {{ $post->category === \App\Models\Post::CATEGORY_TRAVEL ? ($isEnglish ? 'All guides' : 'Všechny návody') : ($post->expedition ? ($isEnglish ? 'Expedition journal' : 'Deník expedice') : ($isEnglish ? 'All stories' : 'Všechny zápisy')) }}</a>
            <p class="article-meta">@if($post->journalDate())<time datetime="{{ $post->journalDateKey() }}">{{ $post->journalDate()->translatedFormat('j. F Y') }}</time>@endif @if($post->location)<span>·</span> {{ $post->location }}@endif</p>
            <h1>{{ $post->title }}</h1>
            <p class="article-lead">{{ $post->excerpt }}</p>
            @if($post->authors->isNotEmpty())<p class="byline">{{ $isEnglish ? 'By' : 'Napsali' }} {{ $post->authors->pluck('name')->join(', ', $isEnglish ? ' and ' : ' a ') }} · {{ $post->readingMinutes() }} {{ $isEnglish ? 'min read' : 'min čtení' }}</p>@endif
            <button id="read-article" class="button article-reader" type="button" aria-pressed="false">{{ $isEnglish ? 'Read article aloud' : 'Přečíst článek nahlas' }}</button>
            <p id="reader-status" class="visually-hidden" role="status" aria-live="polite"></p>
            @if($photoCount || $videoCount)
            <nav class="article-media-summary" aria-label="{{ $isEnglish ? 'Article media' : 'Média v článku' }}">
                @if($photoCount)<a href="#fotografie"><span aria-hidden="true">▧</span> {{ $isEnglish ? $photoCount.' '.($photoCount === 1 ? 'photo' : 'photos') : $post->photoCountLabel() }}</a>@endif
                @if($videoCount)<a href="#videa"><span aria-hidden="true">▶</span> {{ $isEnglish ? $videoCount.' '.($videoCount === 1 ? 'video' : 'videos') : $post->videoCountLabel() }}</a>@endif
            </nav>
            @endif
        </div>
    </header>

    @if($post->cover_image)<figure class="cover-figure"><a class="full-image-link" href="{{ $thumbnails->originalUrl($post->cover_image) }}" data-full-image data-alt="{{ $post->cover_alt }}"><img src="{{ $thumbnails->url($post->cover_image, 'medium') }}" alt="{{ $post->cover_alt }}" width="1440" height="1080"><span>{{ $isEnglish ? 'View full size' : 'Zobrazit v plné velikosti' }}</span></a></figure>@endif

    <div id="article-text" class="article-shell article-body">{!! $post->body !!}</div>

    @if($post->category === \App\Models\Post::CATEGORY_TRAVEL)
        @include('guides._assistance-cta')
    @endif

    @if($photoCount)
    <section id="fotografie" class="article-shell article-gallery anchored-section" aria-labelledby="gallery-title"><h2 id="gallery-title">{{ $isEnglish ? 'Photos from the journey' : 'Fotografie z cesty' }} <small>{{ $isEnglish ? $photoCount.' '.($photoCount === 1 ? 'photo' : 'photos') : $post->photoCountLabel() }}</small></h2><div class="gallery-grid">
        @foreach($post->galleryPhotos() as $photo)<figure><a class="full-image-link" href="{{ $thumbnails->originalUrl($photo['path']) }}" data-full-image data-alt="{{ $photo['alt'] ?? '' }}" data-caption="{{ $photo['caption'] ?? '' }}"><img src="{{ $thumbnails->url($photo['path'], 'medium') }}" alt="{{ $photo['alt'] ?? '' }}" loading="lazy" width="1440" height="1080"><span>{{ $isEnglish ? 'View full size' : 'Zobrazit v plné velikosti' }}</span></a>@if(!empty($photo['caption']))<figcaption>{{ $photo['caption'] }}</figcaption>@endif</figure>@endforeach
    </div></section>
    @endif

    @if($videoCount)
    <section id="videa" class="article-shell video-section anchored-section" aria-labelledby="videos-title"><h2 id="videos-title">{{ $isEnglish ? 'Videos' : 'Videa' }} <small>{{ $isEnglish ? $videoCount.' '.($videoCount === 1 ? 'video' : 'videos') : $post->videoCountLabel() }}</small></h2>
        @foreach($post->videoItems() as $video)
            @php($youtubeId = \App\Support\YouTube::id($video['url'] ?? null))
            <article class="video-item"><h3>{{ $video['title'] }}</h3><p>{{ $video['description'] }}</p>
                @if($youtubeId)<div class="video-frame"><iframe src="https://www.youtube-nocookie.com/embed/{{ $youtubeId }}" title="{{ $video['title'] }}" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe></div>@endif
                <p><a class="text-link ink-link" href="{{ $video['url'] }}">{{ $isEnglish ? 'Watch on YouTube' : 'Přehrát video na YouTube' }}</a></p>
                @if(!empty($video['transcript']))<details><summary>{{ $isEnglish ? 'Video transcript' : 'Přepis videa' }}</summary><div class="transcript">{{ $video['transcript'] }}</div></details>@endif
            </article>
        @endforeach
    </section>
    @endif
</article>

@push('scripts')
<script>
const labels = {{ \Illuminate\Support\Js::from($isEnglish ? ['read' => 'Read article aloud', 'stop' => 'Stop reading', 'stopped' => 'Reading stopped.', 'ended' => 'Article finished.', 'error' => 'Unable to read the article.', 'started' => 'Reading started.', 'locale' => 'en-GB'] : ['read' => 'Přečíst článek nahlas', 'stop' => 'Zastavit čtení', 'stopped' => 'Čtení bylo zastaveno.', 'ended' => 'Čtení článku skončilo.', 'error' => 'Článek se nepodařilo přečíst.', 'started' => 'Čtení článku začalo.', 'locale' => 'cs-CZ']) }};
document.addEventListener('DOMContentLoaded',()=>{const button=document.getElementById('read-article'),article=document.getElementById('article-text'),status=document.getElementById('reader-status');if(!button||!article)return;if(!('speechSynthesis'in window)){button.hidden=true;return}let utterance=null;const stop=()=>{window.speechSynthesis.cancel();utterance=null;button.setAttribute('aria-pressed','false');button.textContent=labels.read;status.textContent=labels.stopped};button.addEventListener('click',()=>{if(utterance){stop();return}utterance=new SpeechSynthesisUtterance(`${@json($post->title)}. ${article.innerText}`);utterance.lang=labels.locale;utterance.onend=()=>{utterance=null;button.setAttribute('aria-pressed','false');button.textContent=labels.read;status.textContent=labels.ended};utterance.onerror=()=>{stop();status.textContent=labels.error};button.setAttribute('aria-pressed','true');button.textContent=labels.stop;status.textContent=labels.started;window.speechSynthesis.speak(utterance)});window.addEventListener('pagehide',()=>window.speechSynthesis.cancel())});
</script>
@endpush

@if($post->cover_image || $photoCount)
<dialog class="image-lightbox" id="image-lightbox" aria-labelledby="image-lightbox-caption">
    <button type="button" class="image-lightbox-close" aria-label="{{ $isEnglish ? 'Close photo' : 'Zavřít fotografii' }}">×</button>
    <figure><img src="" alt=""><figcaption id="image-lightbox-caption"></figcaption></figure>
</dialog>
<script>
document.addEventListener('DOMContentLoaded',()=>{const dialog=document.getElementById('image-lightbox');if(!dialog||typeof dialog.showModal!=='function')return;const image=dialog.querySelector('img'),caption=dialog.querySelector('figcaption'),close=dialog.querySelector('.image-lightbox-close');document.querySelectorAll('[data-full-image]').forEach(link=>link.addEventListener('click',event=>{event.preventDefault();image.src=link.href;image.alt=link.dataset.alt||'';caption.textContent=link.dataset.caption||link.dataset.alt||'';dialog.showModal();close.focus()}));close.addEventListener('click',()=>dialog.close());dialog.addEventListener('click',event=>{if(event.target===dialog)dialog.close()});dialog.addEventListener('close',()=>{image.removeAttribute('src')})});
</script>
@endif
@endsection
