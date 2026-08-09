@extends('layouts.app')
@section('title', 'Deník expedice — Slepé Slunce')
@section('description', 'Přípravy, cesta Španělskem, zatmění a praktické zkušenosti s asistencí pro lidi se zrakovým postižením.')

@section('content')
<header class="page-header"><div class="shell"><p class="eyebrow">Slepé Slunce</p><h1>Deník expedice</h1><p>Od prvního nápadu přes cestu Španělskem až po chvíli, kdy zhasne Slunce.</p></div></header>
<section class="section light-section">
    <div class="shell">
        @if($posts->isEmpty())<div class="empty-state dark-empty"><h2>První zápisy připravujeme</h2><p>Brzy tady najdete přípravy cesty i praktické zkušenosti s asistencí.</p></div>
        @else<div class="card-grid">@foreach($posts as $post) @include('posts._card', ['post' => $post]) @endforeach</div><div class="pagination">{{ $posts->links() }}</div>@endif
    </div>
</section>
@endsection
