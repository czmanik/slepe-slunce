@extends('layouts.app')
@section('title', 'Upravit fotografii — Slepé Slunce')
@section('content')
<div class="quick-route-page"><div class="quick-route-shell"><p class="eyebrow ink">Správa záznamů</p><h1>Upravit fotografii</h1>
@if($errors->any())<div class="quick-errors" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form class="quick-route-form" action="{{ route('mobile.photos.update', $photo) }}" method="post" enctype="multipart/form-data">@csrf @method('PATCH')
<img src="{{ asset('storage/'.$photo->image) }}" alt="{{ $photo->alt }}" style="max-width:100%;max-height:340px;object-fit:contain">
<label>Vyměnit fotografii <span>(nepovinné)</span><input type="file" name="image" accept="image/*"></label>
<label>Expedice<select name="expedition_id" required>@foreach($expeditions as $expedition)<option value="{{ $expedition->id }}" @selected(old('expedition_id', $photo->expedition_id) == $expedition->id)>{{ $expedition->name }}</option>@endforeach</select></label>
<label>Alternativní popis<input name="alt" required maxlength="300" value="{{ old('alt', $photo->alt) }}"></label>
<label>Popisek<textarea name="caption" maxlength="500" rows="3">{{ old('caption', $photo->caption) }}</textarea></label>
<label>Krátký příběh<textarea name="short_story" maxlength="280" rows="3">{{ old('short_story', $photo->short_story) }}</textarea></label>
<label>Pořízeno<input type="datetime-local" name="taken_at" required value="{{ old('taken_at', $photo->taken_at?->format('Y-m-d\TH:i')) }}"></label>
<div class="coordinate-grid"><label>Šířka<input name="latitude" inputmode="decimal" required value="{{ old('latitude', $photo->latitude) }}"></label><label>Délka<input name="longitude" inputmode="decimal" required value="{{ old('longitude', $photo->longitude) }}"></label></div>
<button class="button button-primary" type="submit">Uložit změny</button></form><p class="quick-back"><a href="{{ route('mobile.content.index') }}">← Zpět na záznamy</a></p>
</div></div>
@endsection
