@extends('layouts.app')
@section('title', 'Členové expedice — Slepé Slunce')
@section('description', 'Seznam členů expedice Slepé Slunce a jejich příběhy.')
@section('content')
<header class="page-header"><div class="shell"><p class="eyebrow">Kdo jede</p><h1>Členové expedice</h1><p>Rozdílní lidé, jedna cesta za zatměním.</p></div></header>
<section class="section light-section"><div class="shell members-grid">@forelse($members as $member)<article class="member-card">@if($member->photo)<img src="{{ asset('storage/'.$member->photo) }}" alt="{{ $member->photo_alt ?: $member->name }}">@endif<div><h2>{{ $member->name }}</h2>@if($member->bio)<p>{{ $member->bio }}</p>@endif</div></article>@empty<div class="empty-state dark-empty"><h2>Členy právě doplňujeme</h2></div>@endforelse</div></section>
@endsection
