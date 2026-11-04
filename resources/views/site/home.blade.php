@extends('layouts.site')

@section('title', $school->name)

@section('content')

{{-- Hero: teks + slider foto --}}
<section class="hero">
    <div class="wrap hero-grid {{ $slides->isEmpty() ? 'no-slider' : '' }}">
        <div class="hero-text">
            @if ($school->ppdbIsOpen())
                <span class="pill">● Pendaftaran siswa baru dibuka</span>
            @endif
            <p class="eyebrow">{{ $school->levelLabel() }} · {{ $school->name }}</p>
            <h1>{{ $school->tagline ?: $school->name }}</h1>
            @if ($school->description)
                <p class="lead">{{ $school->description }}</p>
            @endif
            <div class="btns">
                <a class="btn" href="{{ route('ppdb', $school) }}">Pendaftaran Siswa</a>
                <a class="btn btn-outline" href="{{ route('profile', ['school' => $school, 'page' => 'sejarah']) }}">Lihat Profil</a>
            </div>
        </div>

        @include('partials.hero-slider', ['slides' => $slides])
    </div>
</section>

{{-- Akses cepat --}}
<div class="quick">
    <div class="wrap quick-grid">
        <a href="{{ route('ppdb', $school) }}">
            <span class="qi">🎓</span>
            <span><b>Pendaftaran Siswa</b><small>{{ $school->ppdbIsOpen() ? 'Sedang dibuka' : 'Lihat informasi' }}</small></span>
        </a>
        <a href="{{ route('profile', ['school' => $school, 'page' => 'visi-misi']) }}">
            <span class="qi">🏫</span>
            <span><b>Visi dan Misi</b><small>Kenali sekolah kami</small></span>
        </a>
        <a href="{{ route('programs', ['school' => $school, 'page' => 'unggulan']) }}">
            <span class="qi">⭐</span>
            <span><b>Program Unggulan</b><small>Program akademik</small></span>
        </a>
        <a href="{{ route('contact', $school) }}">
            <span class="qi">✉️</span>
            <span><b>Hubungi Kami</b><small>Tanya apa saja</small></span>
        </a>
    </div>
</div>

{{-- Sambutan kepala sekolah --}}
@include('partials.welcome', ['entity' => $school])

{{-- Visi & misi --}}
@if ($school->vision || $school->missionList())
<section class="section alt">
    <div class="wrap">
        <div class="sec-head">
            <span class="kicker">Tentang Kami</span>
            <h2>Visi dan Misi</h2>
        </div>
        @include('partials.vm', ['entity' => $school])
        <div class="center" style="margin-top:1.8rem">
            <a class="btn btn-outline" href="{{ route('profile', ['school' => $school, 'page' => 'visi-misi']) }}">Visi, misi, dan tujuan lengkap</a>
        </div>
    </div>
</section>
@endif

{{-- Keunggulan --}}
@if ($advantages->isNotEmpty())
<section class="section">
    <div class="wrap">
        <div class="sec-head">
            <span class="kicker">Keunggulan</span>
            <h2>Mengapa Memilih {{ $school->name }}?</h2>
        </div>
        <div class="cards cols-4">
            @foreach ($advantages as $a)
                <article class="card center">
                    @if ($a->icon)<div class="icon">{{ $a->icon }}</div>@endif
                    <h3>{{ $a->title }}</h3>
                    <p>{{ $a->description }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- Berita --}}
<section class="section alt">
    <div class="wrap">
        <div class="section-head">
            <h2>Berita dan Informasi Terbaru</h2>
            <a href="{{ route('news', $school) }}">Lihat semua →</a>
        </div>

        @if ($news->isEmpty())
            <p class="muted">Belum ada berita.</p>
        @else
            <div class="cards cols-3">
                @foreach ($news as $post)
                    @include('site._post-card', ['post' => $post])
                @endforeach
            </div>
        @endif
    </div>
</section>

{{-- Galeri --}}
@if ($photos->isNotEmpty())
<section class="section">
    <div class="wrap">
        <div class="section-head">
            <h2>Galeri Kegiatan</h2>
            <a href="{{ route('gallery', $school) }}">Lihat semua →</a>
        </div>
        <div class="photo-grid">
            @foreach ($photos as $p)
                <figure>
                    <a href="{{ route('gallery', $school) }}">
                        <img src="{{ asset('storage/'.$p->image) }}" alt="{{ $p->title }}" loading="lazy">
                    </a>
                    <figcaption>{{ $p->title }}</figcaption>
                </figure>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- Statistik --}}
<section class="section stats">
    <div class="wrap stat-grid">
        @foreach ($stats as $label => $value)
            <div class="stat">
                <div class="num">{{ number_format($value, 0, ',', '.') }}</div>
                <div>{{ $label }}</div>
            </div>
        @endforeach
    </div>
</section>

{{-- FAQ --}}
@if ($faqs->isNotEmpty())
<section class="section">
    <div class="wrap narrow">
        <div class="sec-head">
            <span class="kicker">FAQ</span>
            <h2>Pertanyaan yang Sering Diajukan</h2>
        </div>
        @foreach ($faqs as $f)
            <details class="faq">
                <summary>{{ $f->question }}</summary>
                <p>{!! nl2br(e($f->answer)) !!}</p>
            </details>
        @endforeach
    </div>
</section>
@endif

{{-- Lokasi dan kontak --}}
@include('partials.location', ['entity' => $school, 'contactUrl' => route('contact', $school)])

@endsection