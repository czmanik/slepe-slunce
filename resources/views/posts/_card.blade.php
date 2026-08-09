<article class="post-card">
    <a class="card-image" href="{{ route('posts.show', $post) }}" tabindex="-1" aria-hidden="true">
        @if($post->cover_image)<img src="{{ Storage::url($post->cover_image) }}" alt="" loading="lazy" width="960" height="640">@else<span class="card-placeholder"></span>@endif
    </a>
    <div class="card-body">
        <p class="card-meta">@if($post->published_at)<time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->translatedFormat('j. F Y') }}</time>@endif @if($post->location)<span>·</span> {{ $post->location }}@endif</p>
        <h3><a href="{{ route('posts.show', $post) }}">{{ $post->title }}</a></h3>
        <p>{{ $post->excerpt }}</p>
        @if($post->authors->isNotEmpty())<p class="card-authors">{{ $post->authors->pluck('name')->join(', ') }}</p>@endif
    </div>
</article>
