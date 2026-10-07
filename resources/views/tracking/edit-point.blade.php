@extends('layouts.app')
@section('title', 'Upravit místo — Slepé Slunce')
@section('content')
<div class="quick-route-page"><div class="quick-route-shell"><p class="eyebrow ink">Správa záznamů</p><h1>Upravit místo</h1>
@if($errors->any())<div class="quick-errors" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form class="quick-route-form" action="{{ route('mobile.points.update', $point) }}" method="post">@csrf @method('PATCH')
<label>Expedice<select name="expedition_id" required>@foreach($expeditions as $expedition)<option value="{{ $expedition->id }}" @selected(old('expedition_id', $point->expedition_id) == $expedition->id)>{{ $expedition->name }}</option>@endforeach</select></label>
<label>Název místa<input name="name" maxlength="160" required value="{{ old('name', $point->name) }}"></label>
<label>Popis<textarea name="description" maxlength="700" rows="3">{{ old('description', $point->description) }}</textarea></label>
<label>Datum a čas<input type="datetime-local" name="occurred_at" required value="{{ old('occurred_at', $point->occurred_at?->format('Y-m-d\TH:i')) }}"></label>
<div class="coordinate-grid"><label>Šířka<input name="latitude" inputmode="decimal" required value="{{ old('latitude', $point->latitude) }}"></label><label>Délka<input name="longitude" inputmode="decimal" required value="{{ old('longitude', $point->longitude) }}"></label></div>
<label>Stav<select name="status" required>@foreach($statuses as $value => $label)<option value="{{ $value }}" @selected(old('status', $point->status->value) === $value)>{{ $label }}</option>@endforeach</select></label>
<label class="checkbox-row"><input type="checkbox" name="is_goal" value="1" @checked(old('is_goal', $point->is_goal))> Důležitý cíl</label>
<button class="button button-primary" type="submit">Uložit změny</button></form><p class="quick-back"><a href="{{ route('mobile.content.index') }}">← Zpět na záznamy</a></p>
</div></div>
@endsection
