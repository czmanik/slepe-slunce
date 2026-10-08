@extends('layouts.app')
@section('title', ($photo->caption ?: $photo->alt).' — '.(app()->isLocale('en') ? 'Blind Sun' : 'Slepé Slunce'))
@section('content')
<section class="section light-section"><div class="shell narrow-section">
<p class="eyebrow ink">{{ $photo->expedition->name }} · {{ $photo->taken_at?->translatedFormat('j. n. Y H:i') }}</p>
<h1>{{ $photo->caption ?: $photo->alt }}</h1>
<a href="{{ asset('storage/'.$photo->image) }}" target="_blank" rel="noopener" aria-label="{{ app()->isLocale('en') ? 'Open full-size photo' : 'Otevřít fotografii v plné velikosti' }}"><img src="{{ $displayImage }}" alt="{{ $photo->alt }}" style="display:block;width:100%;height:auto;max-height:80vh;object-fit:contain"></a>
@if($photo->short_story)<p>{{ $photo->short_story }}</p>@endif
<p><a href="{{ asset('storage/'.$photo->image) }}" target="_blank" rel="noopener">{{ app()->isLocale('en') ? 'Open original photo' : 'Otevřít originál fotografie' }}</a> · <a href="{{ route('map.index', ['expedition' => $photo->expedition->slug]) }}">{{ app()->isLocale('en') ? 'Back to expedition map' : 'Zpět na mapu expedice' }}</a></p>
</div></section>
@endsection
