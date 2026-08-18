<article class="post-card">
    <a class="card-image" href="{{ route('posts.show', $post) }}" tabindex="-1" aria-hidden="true">
        @if($post->cover_image)<img src="{{ app(\App\Services\ImageThumbnail::class)->url($post->cover_image, 'small') }}" alt="" loading="lazy" width="720" height="540">@else<span class="card-placeholder"></span>@endif
    </a>
    <div class="card-body">
        <p class="card-meta">@if($post->journalDate())<time datetime="{{ $post->journalDateKey() }}">{{ $post->journalDate()->translatedFormat('j. F Y') }}</time>@endif @if($post->location)<span>·</span> {{ $post->location }}@endif</p>
        <h3><a href="{{ route('posts.show', $post) }}">{{ $post->title }}</a></h3>
        <p>{{ $post->excerpt }}</p>
        @if($post->authors->isNotEmpty())<p class="card-authors">{{ $post->authors->pluck('name')->join(', ') }}</p>@endif
    </div>
</article>
