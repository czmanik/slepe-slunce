@extends('layouts.app')
@section('title', 'Aplikace pro Android — Slepé Slunce')
@section('content')
<section class="section light-section"><div class="shell narrow-section"><p class="eyebrow ink">Na cestě</p><h1>Slepé Slunce pro Android</h1>
<p>Přihlaste se svým účtem, přidávejte místa a fotografie přímo z telefonu a spravujte jejich zařazení do expedic.</p>
@if($release)
<p>Aktuální verze: <strong>{{ $release['version_name'] }}</strong></p>
<p><a class="button button-primary" href="{{ $release['download_url'] }}" download>Stáhnout APK pro Android</a></p>
<p>Po stažení otevřete soubor v telefonu. Android může požádat o povolení instalace z tohoto zdroje. Aktualizaci instalujte přes stávající aplikaci.</p>
@else
<p>Instalační soubor právě připravujeme. Vraťte se sem později.</p>
@endif
</div></section>
@endsection
