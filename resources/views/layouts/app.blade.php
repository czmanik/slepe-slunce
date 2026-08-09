<!doctype html>
<html lang="cs">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Slepé Slunce — expedice za zatměním')</title>
    <meta name="description" content="@yield('description', 'Čtyři kamarádi jedou do Španělska za úplným zatměním Slunce. O cestě, asistenci a snech bez zbytečného patosu.')">
    <meta name="theme-color" content="#17150f">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:title" content="@yield('title', 'Slepé Slunce — expedice za zatměním')">
    <meta property="og:description" content="@yield('description', 'Čtyři kamarádi jedou do Španělska za úplným zatměním Slunce.')">
    @hasSection('og_image')<meta property="og:image" content="@yield('og_image')">@endif
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="stylesheet" href="{{ asset('assets/site.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/route.css') }}">
    @stack('head')
</head>
<body>
    <a class="skip-link" href="#hlavni-obsah">Přeskočit na hlavní obsah</a>
    <header class="site-header">
        <div class="shell header-inner">
            <a class="brand" href="{{ route('home') }}" aria-label="Slepé Slunce, úvodní stránka">
                <span class="brand-mark" aria-hidden="true"><span></span></span>
                <span>Slepé Slunce</span>
            </a>
            <nav aria-label="Hlavní navigace">
                <a href="{{ route('home') }}#expedice">Expedice</a>
                <a href="{{ route('home') }}#smysl">Proč jedeme</a>
                <a href="{{ route('posts.index') }}" @if(request()->routeIs('posts.*')) aria-current="page" @endif>Deník</a>
                <a href="{{ route('route.index') }}" @if(request()->routeIs('route.*')) aria-current="page" @endif>Trasa</a>
            </nav>
        </div>
    </header>

    <main id="hlavni-obsah" tabindex="-1">@yield('content')</main>

    <footer class="site-footer">
        <div class="shell footer-grid">
            <div><strong>Slepé Slunce</strong><p>Výprava čtyř kamarádů za úplným zatměním Slunce ve Španělsku.</p></div>
            <div><p>Projekt vzniká ve spolupráci s Mirkem Mužíkem, členem SONS a spoluzakladatelem spolku Úraz.</p></div>
        </div>
    </footer>
    @stack('scripts')
</body>
</html>
