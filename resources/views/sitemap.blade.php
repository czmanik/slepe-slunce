<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url><loc>{{ route('home') }}</loc></url>
    <url><loc>{{ route('posts.index') }}</loc></url>
    <url><loc>{{ route('route.index') }}</loc></url>
    <url><loc>{{ route('members.index') }}</loc></url>
    @foreach($posts as $post)<url><loc>{{ route('posts.show', $post) }}</loc><lastmod>{{ $post->updated_at->toAtomString() }}</lastmod></url>@endforeach
</urlset>
