@extends('layouts.portal')

@section('title', $post->title.' — '.$foundation->name)
@section('description', $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->body), 150))

@section('content')
<article class="section">
    <div class="wrap narrow">
        <p><a href="{{ route('portal.news') }}">← Semua berita</a></p>
        <span class="tag {{ $post->type === 'pengumuman' ? 'tag-warn' : '' }}">{{ $post->typeLabel() }}</span>
        <h1>{{ $post->title }}</h1>
        <p class="muted">{{ $post->published_at?->translatedFormat('l, d F Y') }}</p>

        @if ($post->image)
            <img class="article-img" src="{{ asset('storage/'.$post->image) }}" alt="{{ $post->title }}">
        @endif

        <div class="prose">{!! nl2br(e($post->body)) !!}</div>
    </div>
</article>

@if ($others->isNotEmpty())
<section class="section alt">
    <div class="wrap">
        <h2>Tulisan lainnya</h2>
        <div class="cards cols-3">
            @foreach ($others as $post)
                @include('portal._post-card', ['post' => $post])
            @endforeach
        </div>
    </div>
</section>
@endif
@endsection