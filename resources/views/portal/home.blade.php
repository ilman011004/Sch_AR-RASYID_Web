@extends('layouts.portal')

@section('title', $foundation->name)

@section('content')

<section class="hero">
    <div class="wrap hero-grid">
        <div class="hero-text">
            <p class="eyebrow">Yayasan Pendidikan</p>
            <h1>{{ $foundation->tagline ?: $foundation->name }}</h1>
            @if ($foundation->description)
                <p class="lead">{{ $foundation->description }}</p>
            @endif
            <div class="btns">
                <a class="btn" href="{{ route('portal.profile', 'sejarah') }}">Tentang Yayasan</a>
                <a class="btn btn-outline" href="{{ route('portal.spmb') }}">Info SPMB</a>
            </div>
        </div>

        <figure class="head-card">
            @if ($foundation->headPhotoUrl())
                <img src="{{ $foundation->headPhotoUrl() }}" alt="{{ $foundation->head_title }} {{ $foundation->head_name }}">
            @else
                <div class="avatar" aria-hidden="true">{{ mb_substr((string) $foundation->head_name ?: $foundation->name, 0, 1) }}</div>
            @endif
            <figcaption>
                @if ($foundation->welcome_message)
                    <blockquote>{{ $foundation->welcome_message }}</blockquote>
                @endif
                <strong>{{ $foundation->head_name }}</strong>
                <span>{{ $foundation->head_title }}</span>
            </figcaption>
        </figure>
    </div>
</section>

{{-- Jenjang --}}
<section class="section" id="jenjang">
    <div class="wrap">
        <h2 class="center">Jenjang Pendidikan</h2>
        <div class="cards cols-4">
            @foreach ($jenjang as $s)
                @if ($s->is_active)
                    <a class="card center portal-card" style="--primary: {{ $s->safeColor() }}" href="{{ route('home', $s) }}">
                        @if ($s->logoUrl())<img class="logo-lg" src="{{ $s->logoUrl() }}" alt="">@endif
                        <h3>{{ $s->name }}</h3>
                        <p class="muted">{{ $s->levelLabel() }}</p>
                    </a>
                @else
                    <div class="card center portal-card" style="--primary: {{ $s->safeColor() }}; opacity:.6">
                        <h3>{{ $s->name }}</h3>
                        <p class="muted">Segera hadir</p>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</section>

{{-- SPMB --}}
@php $openSchools = $jenjang->filter(fn ($s) => $s->is_active && $s->ppdbIsOpen()); @endphp
<section class="section alt">
    <div class="wrap narrow center">
        <h2>Penerimaan Siswa Baru (SPMB)</h2>
        @if ($openSchools->isNotEmpty())
            <p>Pendaftaran sedang dibuka untuk:</p>
            <div class="btns" style="justify-content:center">
                @foreach ($openSchools as $s)
                    <a class="btn" href="{{ route('ppdb', $s) }}">Daftar {{ $s->name }}</a>
                @endforeach
            </div>
        @else
            <p class="muted">Pendaftaran belum dibuka. Pantau terus halaman pengumuman.</p>
        @endif
        <p style="margin-top:1rem">
            <a href="{{ route('portal.spmb') }}">Informasi pendaftaran →</a> ·
            <a href="{{ route('portal.announcements') }}">Pengumuman →</a>
        </p>
    </div>
</section>

{{-- Berita --}}
<section class="section">
    <div class="wrap">
        <div class="section-head">
            <h2>Berita dan Informasi Terbaru</h2>
            <a href="{{ route('portal.news') }}">Lihat semua →</a>
        </div>

        @if ($news->isEmpty())
            <p class="muted">Belum ada berita.</p>
        @else
            <div class="cards cols-3">
                @foreach ($news as $post)
                    @include('portal._post-card', ['post' => $post])
                @endforeach
            </div>
        @endif
    </div>
</section>
@endsection