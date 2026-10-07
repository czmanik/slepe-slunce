<!doctype html>
<html lang="{{ $siteLocale ?? app()->getLocale() }}">
<head>
    @php
        $isEnglish = $isEnglishSite ?? app()->isLocale('en');
        $czechUrl = rtrim(config('international.czech_url'), '/').request()->getRequestUri();
        $englishUrl = rtrim(config('international.english_url'), '/').request()->getRequestUri();
        $googleTranslateUrl = 'https://translate.google.com/translate?'.http_build_query([
            'sl' => 'cs',
            'tl' => 'en',
            'u' => url()->full(),
        ]);
        $defaultTitle = $isEnglish
            ? 'Blind Sun — travelling together without barriers'
            : 'Slepé Slunce — cestujeme spolu bez bariér';
        $defaultDescription = $isEnglish
            ? 'Friends creating accessible expeditions and sharing life without unnecessary barriers.'
            : 'Parta kamarádů pořádá přístupné expedice a sdílí zkušenosti ze života nevidomých a lidí s handicapem.';
    @endphp
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', $defaultTitle)</title>
    <meta name="description" content="@yield('description', $defaultDescription)">
    <meta name="theme-color" content="#17150f">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:title" content="@yield('title', $defaultTitle)">
    <meta property="og:description" content="@yield('description', $defaultDescription)">
    @hasSection('og_image')<meta property="og:image" content="@yield('og_image')">@endif
    <link rel="canonical" href="{{ $isEnglish ? $englishUrl : $czechUrl }}">
    <link rel="alternate" hreflang="cs" href="{{ $czechUrl }}">
    <link rel="alternate" hreflang="en" href="{{ $englishUrl }}">
    <link rel="alternate" hreflang="x-default" href="{{ $czechUrl }}">
    <link rel="stylesheet" href="{{ asset('assets/site.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/route.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/expedition.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/map-photo.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/journal.css') }}?v={{ filemtime(public_path('assets/journal.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/guides.css') }}?v={{ filemtime(public_path('assets/guides.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/platform.css') }}?v={{ filemtime(public_path('assets/platform.css')) }}">
    @php($sentryLoader = config('services.sentry.browser_loader_url'))
    @if(is_string($sentryLoader) && preg_match('~^https://js\.sentry-cdn\.com/[A-Za-z0-9_-]+\.min\.js$~', $sentryLoader))
        <script>
            window.sentryOnLoad = function () {
                Sentry.init({
                    tracesSampleRate: {{ max(0, min(1, (float) config('services.sentry.browser_traces_sample_rate', 0.1))) }},
                    sendDefaultPii: false,
                    replaysSessionSampleRate: 0,
                    replaysOnErrorSampleRate: 0
                });
            };
        </script>
        <script src="{{ $sentryLoader }}" crossorigin="anonymous"></script>
    @endif
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
    <a class="skip-link" href="#hlavni-obsah">{{ $isEnglish ? 'Skip to main content' : 'Přeskočit na hlavní obsah' }}</a>
    <header class="site-header">
        <div class="shell header-inner">
            <a class="brand" href="{{ route('home') }}" aria-label="{{ $isEnglish ? 'Blind Sun, home page' : 'Slepé Slunce, úvodní stránka' }}">
                <span class="brand-mark" aria-hidden="true"><span></span></span>
                <span>{{ $isEnglish ? 'Blind Sun' : 'Slepé Slunce' }}</span>
            </a>
            <nav aria-label="{{ $isEnglish ? 'Main navigation' : 'Hlavní navigace' }}">
                <a href="{{ route('expeditions.index') }}" @if(request()->routeIs('expeditions.index')) aria-current="page" @endif>{{ $isEnglish ? 'Expeditions' : 'Expedice' }}</a>
                <a href="{{ route('map.index') }}" @if(request()->routeIs('map.index')) aria-current="page" @endif>{{ $isEnglish ? 'Map' : 'Mapa' }}</a>
                <a href="{{ route('posts.index') }}" @if(request()->routeIs('posts.*') || request()->routeIs('expeditions.posts')) aria-current="page" @endif>{{ $isEnglish ? 'Journal' : 'Deník' }}</a>
                <a href="{{ route('guides.index') }}" @if(request()->routeIs('guides.*')) aria-current="page" @endif>{{ $isEnglish ? 'Guides' : 'Návody' }}</a>
                <a href="{{ route('home') }}#smysl">{{ $isEnglish ? 'About' : 'O projektu' }}</a>
                <a href="{{ route('home') }}#odber">{{ $isEnglish ? 'Updates' : 'Odběr' }}</a>
            </nav>
            <nav class="language-switcher" aria-label="{{ $isEnglish ? 'Language selection' : 'Výběr jazyka' }}">
                <a href="{{ $czechUrl }}" lang="cs" @if(!$isEnglish) aria-current="true" @endif>Česky</a>
                <a href="{{ $englishUrl }}" lang="en" @if($isEnglish) aria-current="true" @endif>English</a>
            </nav>
        </div>
        @if($isEnglish)
            <div class="translation-notice"><div class="shell"><strong>Blind Sun is a Czech initiative, currently based in Estepona, Spain.</strong> Some stories are currently available in Czech. <a href="{{ $googleTranslateUrl }}" target="_blank" rel="noopener noreferrer">Translate this page automatically with Google Translate</a>.</div></div>
        @endif
        @isset($expedition)
            <nav class="expedition-nav" aria-label="{{ $isEnglish ? 'Expedition navigation' : 'Navigace expedice' }} {{ $expedition->name }}">
                <div class="shell">
                    <strong>{{ $expedition->name }}</strong>
                    <a href="{{ route('expeditions.show', $expedition) }}" @if(request()->routeIs('expeditions.show')) aria-current="page" @endif>{{ $isEnglish ? 'Overview' : 'Přehled' }}</a>
                    <a href="{{ route('expeditions.posts', $expedition) }}" @if(request()->routeIs('expeditions.posts')) aria-current="page" @endif>{{ $isEnglish ? 'Journal' : 'Deník' }}</a>
                    <a href="{{ route('map.index', ['expedition' => $expedition->slug]) }}" @if(request()->routeIs('map.index') && request('expedition') === $expedition->slug) aria-current="page" @endif>{{ $isEnglish ? 'Map' : 'Mapa' }}</a>
                    <a href="{{ route('expeditions.members', $expedition) }}" @if(request()->routeIs('expeditions.members')) aria-current="page" @endif>{{ $isEnglish ? 'Members' : 'Členové' }}</a>
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
            <div><strong>{{ $isEnglish ? 'Blind Sun' : 'Slepé Slunce' }}</strong><p>{{ $isEnglish ? 'Friends organising accessible expeditions and sharing life without unnecessary barriers.' : 'Parta kamarádů, která pořádá přístupné expedice a sdílí život bez zbytečných bariér.' }}</p><p><a href="https://www.instagram.com/slepeslunce/" target="_blank" rel="noopener noreferrer">Instagram @slepeslunce</a></p></div>
            <div><p>{{ $isEnglish ? 'The project is created with Mirek Mužík, a member of SONS Czech Republic and co-founder of the Odškodnění za úraz association.' : 'Projekt vzniká ve spolupráci s Mirkem Mužíkem, členem ' }}@if(!$isEnglish)<a href="https://www.sons.cz/">SONS ČR</a>{{ $isEnglish ? '' : ' a spoluzakladatelem spolku ' }}@endif@if(!$isEnglish)<a href="https://odskodnenizauraz.cz/">Odškodnění za úraz</a>{{ $isEnglish ? '' : '.' }}@endif</p></div>
        </div>
    </footer>
    @stack('scripts')
</body>
</html>
