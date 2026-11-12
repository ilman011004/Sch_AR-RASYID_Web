@extends('layouts.portal')

@php
    // Dikirim dari controller: berita lain dan pengumuman lain (tanpa tulisan yang sedang dibuka)
    $otherNews = $otherNews ?? collect();
    $announcements = $announcements ?? collect();
@endphp

@section('title', $post->title.' — '.$foundation->name)
@section('description', $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->body), 150))

@section('content')
<section class="section">
    <div class="wrap">
        <div class="article-layout">

            {{-- Isi tulisan yang sedang dibuka (berita maupun pengumuman) --}}
            <article class="article-main">
                <p><a class="back-link" href="{{ route('portal.news') }}">← Semua berita & pengumuman</a></p>
                <div class="article-header">
                    <span class="tag {{ $post->type === 'pengumuman' ? 'tag-warn' : '' }}">{{ $post->typeLabel() }}</span>
                    <h1>{{ $post->title }}</h1>
                    <p class="muted">{{ $post->published_at?->translatedFormat('l, d F Y') }}</p>
                </div>

                @if ($post->image)
                    <div class="article-img-wrap">
                        <img class="article-img" src="{{ asset('storage/'.$post->image) }}" alt="{{ $post->title }}">
                    </div>
                @endif

                <div class="prose">{!! nl2br(e($post->body)) !!}</div>
            </article>

            {{-- Samping: Berita Lainnya, lalu di bawahnya Pengumuman Lainnya --}}
            <aside class="article-sidebar">

                @if ($otherNews->isNotEmpty())
                    <div class="sidebar-widget">
                        <h3 class="sidebar-widget-title">📰 Berita Lainnya</h3>
                        <div class="sidebar-post-list">
                            @foreach ($otherNews as $item)
                                <a class="sidebar-post-item" href="{{ $item->publicUrl() }}">
                                    @if ($item->image)
                                        <img class="sidebar-post-thumb" src="{{ asset('storage/'.$item->image) }}" alt="{{ $item->title }}" loading="lazy">
                                    @endif
                                    <div class="sidebar-post-info">
                                        <div class="sidebar-post-title">{{ $item->title }}</div>
                                        <div class="sidebar-post-meta">
                                            <span>{{ $item->school->isFoundation() ? 'Yayasan' : $item->school->name }}</span>
                                            <span>·</span>
                                            <span>{{ $item->published_at?->translatedFormat('d M Y') }}</span>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                        <a class="more" href="{{ route('portal.news', ['jenis' => 'berita']) }}">Semua berita →</a>
                    </div>
                @endif

                @if ($announcements->isNotEmpty())
                    <div class="sidebar-widget">
                        <h3 class="sidebar-widget-title">📢 Pengumuman Lainnya</h3>
                        <div class="sidebar-post-list">
                            @foreach ($announcements as $item)
                                <a class="sidebar-post-item" href="{{ $item->publicUrl() }}">
                                    @if ($item->image)
                                        <img class="sidebar-post-thumb" src="{{ asset('storage/'.$item->image) }}" alt="{{ $item->title }}" loading="lazy">
                                    @endif
                                    <div class="sidebar-post-info">
                                        <div class="sidebar-post-title">{{ $item->title }}</div>
                                        <div class="sidebar-post-meta">
                                            <span class="tag-warn-sm">Pengumuman</span>
                                            <span>{{ $item->school->isFoundation() ? 'Yayasan' : $item->school->name }}</span>
                                            <span>·</span>
                                            <span>{{ $item->published_at?->translatedFormat('d M Y') }}</span>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                        <a class="more" href="{{ route('portal.news', ['jenis' => 'pengumuman']) }}">Semua pengumuman →</a>
                    </div>
                @endif

            </aside>
        </div>
    </div>
</section>
@endsection