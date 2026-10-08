@extends('layouts.app')
@section('title', app()->isLocale('en') ? 'Android app — Blind Sun' : 'Aplikace pro Android — Slepé Slunce')
@section('content')
<section class="section light-section"><div class="shell narrow-section"><p class="eyebrow ink">{{ app()->isLocale('en') ? 'On the road' : 'Na cestě' }}</p><h1>{{ app()->isLocale('en') ? 'Blind Sun for Android' : 'Slepé Slunce pro Android' }}</h1>
<p>{{ app()->isLocale('en') ? 'Sign in, add places and photos from your phone, and organise them by expedition.' : 'Přihlaste se svým účtem, přidávejte místa a fotografie přímo z telefonu a spravujte jejich zařazení do expedic.' }}</p>
@if($release)
<p>{{ app()->isLocale('en') ? 'Current version:' : 'Aktuální verze:' }} <strong>{{ $release['version_name'] }}</strong></p>
<p><a class="button button-primary" href="{{ $release['download_url'] }}" download>{{ app()->isLocale('en') ? 'Download Android APK' : 'Stáhnout APK pro Android' }}</a></p>
<p>{{ app()->isLocale('en') ? 'Open the downloaded file on your phone. Android may ask you to allow installation from this source. Install updates over the existing app.' : 'Po stažení otevřete soubor v telefonu. Android může požádat o povolení instalace z tohoto zdroje. Aktualizaci instalujte přes stávající aplikaci.' }}</p>
@else
<p>{{ app()->isLocale('en') ? 'The app download is being prepared. Please come back later.' : 'Instalační soubor právě připravujeme. Vraťte se sem později.' }}</p>
@endif
</div></section>
@endsection
