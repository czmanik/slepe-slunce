@extends('layouts.app')
@section('title', 'Členové — '.$expedition->name)
@section('description', 'Seznam členů expedice Slepé Slunce a jejich příběhy.')
@section('content')
<header class="page-header"><div class="shell"><p class="eyebrow">{{ $expedition->name }}</p><h1>Členové expedice</h1><p>Každý má vlastní roli, společně tvoříme jeden tým.</p></div></header>
<section class="section light-section"><div class="shell members-grid">@forelse($members as $member)<article class="member-card">@if($member->photo)<img src="{{ asset('storage/'.$member->photo) }}" alt="{{ $member->photo_alt ?: '' }}">@endif<div><h2>{{ $member->name }}</h2>@if($member->pivot->role)<p><strong>{{ $member->pivot->role }}</strong></p>@endif @if($member->pivot->expedition_bio || $member->bio)<p>{{ $member->pivot->expedition_bio ?: $member->bio }}</p>@endif</div></article>@empty<div class="empty-state dark-empty"><h2>Členy právě doplňujeme</h2></div>@endforelse</div></section>
@endsection
