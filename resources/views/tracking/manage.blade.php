@extends('layouts.app')
@section('title', 'Správa záznamů — Slepé Slunce')
@section('content')
<section class="section light-section"><div class="shell"><p class="eyebrow ink">Na cestě</p><h1>Správa záznamů</h1>
<p>Upravte expedici, datum, polohu i popis posledních fotografií a míst.</p>
<div class="button-row"><a class="button button-primary" href="{{ route('tracking.photo.create') }}">Přidat fotografii</a>@can('create', \App\Models\RoutePoint::class)<a class="button button-quiet" href="{{ route('route.quick.create') }}">Přidat místo</a>@endcan</div>
<h2>Fotografie</h2><div class="card-grid">
@forelse($photos as $photo)
<article class="content-card"><img src="{{ app(\App\Services\ImageThumbnail::class)->url($photo->image, 'small') }}" alt="{{ $photo->alt }}" loading="lazy" style="width:100%;height:180px;object-fit:cover"><h3>{{ $photo->caption ?: $photo->alt }}</h3><p>{{ $photo->expedition?->name }} · {{ $photo->taken_at?->translatedFormat('j. n. Y H:i') }}</p><a href="{{ route('mobile.photos.edit', $photo) }}">Upravit fotografii →</a></article>
@empty<p>Zatím tu nejsou fotografie k úpravě.</p>@endforelse
</div>
@if($points->isNotEmpty())<h2>Místa</h2><div class="card-grid">@foreach($points as $point)<article class="content-card"><h3>{{ $point->name }}</h3><p>{{ $point->expedition?->name }} · {{ $point->occurred_at?->translatedFormat('j. n. Y H:i') }}</p><a href="{{ route('mobile.points.edit', $point) }}">Upravit místo →</a></article>@endforeach</div>@endif
</div></section>
@endsection
