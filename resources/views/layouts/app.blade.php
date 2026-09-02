<!doctype html>
<html lang="cs">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Slepé Slunce — cestujeme spolu bez bariér')</title>
    <meta name="description" content="@yield('description', 'Parta kamarádů pořádá přístupné expedice a sdílí zkušenosti ze života nevidomých a lidí s handicapem.')">
    <meta name="theme-color" content="#17150f">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:title" content="@yield('title', 'Slepé Slunce — cestujeme spolu bez bariér')">
    <meta property="og:description" content="@yield('description', 'Parta kamarádů pořádá přístupné expedice a sdílí zkušenosti ze života nevidomých a lidí s handicapem.')">
    @hasSection('og_image')<meta property="og:image" content="@yield('og_image')">@endif
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="stylesheet" href="{{ asset('assets/site.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/route.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/expedition.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/map-photo.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/journal.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/platform.css') }}">
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-REWW639R3N"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-REWW639R3N');
    </script>
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
                <a href="{{ route('expeditions.index') }}" @if(request()->routeIs('expeditions.index')) aria-current="page" @endif>Expedice</a>
                <a href="{{ route('posts.index') }}" @if(request()->routeIs('posts.*')) aria-current="page" @endif>Články</a>
                <a href="{{ route('home') }}#smysl">O projektu</a>
                <a href="{{ route('home') }}#odber">Odběr</a>
            </nav>
        </div>
        @isset($expedition)
            <nav class="expedition-nav" aria-label="Navigace expedice {{ $expedition->name }}">
                <div class="shell">
                    <strong>{{ $expedition->name }}</strong>
                    <a href="{{ route('expeditions.show', $expedition) }}" @if(request()->routeIs('expeditions.show')) aria-current="page" @endif>Přehled</a>
                    <a href="{{ route('expeditions.route', $expedition) }}" @if(request()->routeIs('expeditions.route')) aria-current="page" @endif>Program a trasa</a>
                    <a href="{{ route('expeditions.posts', $expedition) }}" @if(request()->routeIs('expeditions.posts')) aria-current="page" @endif>Deník</a>
                    <a href="{{ route('expeditions.members', $expedition) }}" @if(request()->routeIs('expeditions.members')) aria-current="page" @endif>Členové</a>
                </div>
            </nav>
        @endisset
    </header>

    <main id="hlavni-obsah" tabindex="-1">
        @if(session('message'))<div class="flash-message" role="status"><div class="shell">{{ session('message') }}</div></div>@endif
        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="shell footer-grid">
            <div><strong>Slepé Slunce</strong><p>Parta kamarádů, která pořádá přístupné expedice a sdílí život bez zbytečných bariér.</p></div>
            <div><p>Projekt vzniká ve spolupráci s Mirkem Mužíkem, členem <a href="https://www.sons.cz/">SONS ČR</a> a spoluzakladatelem spolku <a href="https://odskodnenizauraz.cz/">Odškodnění za úraz</a>.</p></div>
        </div>
    </footer>
    @stack('scripts')
</body>
</html>
