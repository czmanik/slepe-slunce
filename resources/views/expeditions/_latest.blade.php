@if($latestContent->isNotEmpty())
<section class="section journal-section"><div class="shell"><div class="section-heading"><div><p class="eyebrow">Z cest</p><h2>Nejnovější</h2></div><a class="text-link" href="{{ route('map.index', isset($expedition) ? ['expedition' => $expedition->slug] : []) }}">Prohlédnout mapu →</a></div>
<div class="card-grid">@foreach($latestContent as $item)<article class="content-card">
@if($item['image'])<a href="{{ $item['url'] }}"><img src="{{ $item['image'] }}" alt="{{ $item['alt'] ?: '' }}" loading="lazy" style="width:100%;height:220px;object-fit:cover"></a>@endif
<p class="eyebrow">{{ $item['type'] }} · {{ $item['expedition'] }} @if($item['date'])· <time datetime="{{ $item['date']->toIso8601String() }}">{{ $item['date']->translatedFormat('j. n. Y') }}</time>@endif</p>
<h3><a href="{{ $item['url'] }}">{{ $item['title'] }}</a></h3>@if($item['description'])<p>{{ $item['description'] }}</p>@endif
</article>@endforeach</div></div></section>
@endif
