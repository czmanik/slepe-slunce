@if($record->cover_image)
    <figure><img src="{{ asset('storage/'.$record->cover_image) }}" alt="{{ $record->cover_alt }}" loading="lazy" width="960" height="640"></figure>
@endif
@if($record->gallery)
    <div class="route-media-grid">
        @foreach($record->gallery as $image)
            <figure>
                <img src="{{ asset('storage/'.$image['path']) }}" alt="{{ $image['alt'] }}" loading="lazy" width="640" height="480">
                @if(!empty($image['caption']))<figcaption>{{ $image['caption'] }}</figcaption>@endif
            </figure>
        @endforeach
    </div>
@endif
@if($record->videos)
    <ul class="route-videos" aria-label="Videa {{ $label }}">
        @foreach($record->videos as $video)
            <li><a href="{{ $video['url'] }}" rel="noopener noreferrer">{{ $video['title'] }}</a>@if(!empty($video['description'])) — {{ $video['description'] }}@endif</li>
        @endforeach
    </ul>
@endif
