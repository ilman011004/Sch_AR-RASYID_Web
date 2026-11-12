@extends('layouts.portal')

@section('title', $foundation->name)
@section('body_class', 'hero-page')

@section('content')

{{-- Hero: teks + slider foto --}}
<section class="hero">
    <div class="wrap hero-grid {{ $slides->isEmpty() ? 'no-slider' : '' }}">
        <div class="hero-text">
            @php
                $title = $foundation->tagline ?: $foundation->name;
                $len = mb_strlen($title);
                // Makin panjang judul, makin kecil ukurannya
                $size = $len > 60 ? 'clamp(1.6rem, 3vw, 2.4rem)'
                      : ($len > 35 ? 'clamp(2rem, 4vw, 3.2rem)'
                      : 'clamp(2.4rem, 5vw, 4rem)');
                // Tiap kata dikunci agar "AR-Rasyid" tidak terpotong jadi "AR-" dan "Rasyid"
                $titleHtml = preg_replace('/(\S+)/u', '<span style="white-space:nowrap">$1</span>', e($title));
            @endphp
            <h1 style="font-size: {{ $size }}; text-wrap: balance;">{!! $titleHtml !!}</h1>
            @if ($foundation->description)
                <p class="lead">{{ $foundation->description }}</p>
            @endif
            <div class="btns">
                <a class="btn" href="{{ route('portal.spmb') }}">Info SPMB</a>
                <a class="btn btn-outline" href="{{ route('portal.profile', 'sejarah') }}">Tentang Yayasan</a>
            </div>
        </div>

        @include('partials.hero-slider', ['slides' => $slides])
    </div>
</section>

{{-- Akses cepat --}}
<div class="quick">
    <div class="wrap quick-grid">
        <a href="{{ route('portal.spmb') }}">
            <span class="qi">🎓</span>
            <span><b>Info SPMB</b><small>Pendaftaran siswa baru</small></span>
        </a>
        <a href="#jenjang">
            <span class="qi">🏫</span>
            <span><b>Jenjang Pendidikan</b><small>PAUD sampai SMA</small></span>
        </a>
        <a href="{{ route('portal.profile', 'visi-misi') }}">
            <span class="qi">🎯</span>
            <span><b>Visi dan Misi</b><small>Arah yayasan</small></span>
        </a>
        <a href="{{ route('portal.news') }}">
            <span class="qi">📰</span>
            <span><b>Berita</b><small>Kabar terbaru</small></span>
        </a>
    </div>
</div>

{{-- Sambutan ketua kini tampil di bagian Pendiri dan Ketua (lebih bawah). Untuk menampilkan sambutan lengkap lagi, aktifkan baris berikut:
@include('partials.welcome', ['entity' => $foundation])
--}}

{{-- Tentang Yayasan: foto di kiri, teks di kanan. Hanya tampil bila teks atau fotonya sudah diisi di admin --}}
@if (filled($foundation->about_text) || filled($foundation->about_photo))
<style>
    .about { background: #fff; }
    .about-grid {
        display: grid; grid-template-columns: minmax(0, 5fr) minmax(0, 6fr);
        gap: clamp(2rem, 5vw, 4.5rem); align-items: center;
    }
    .about-grid.no-photo { grid-template-columns: minmax(0, 1fr); max-width: 760px; margin-inline: auto; }
    .about-media { position: relative; }
    .about-media::before {
        content: ""; position: absolute; z-index: 0; inset: 16px -16px -16px 16px; border-radius: 26px;
        border: 2px solid var(--accent-2, #f5b800);
    }
    .about-media img {
        position: relative; z-index: 1; display: block; width: 100%; aspect-ratio: 4 / 3; object-fit: cover;
        border-radius: 22px; box-shadow: 0 30px 60px -28px rgba(16, 24, 40, .45);
    }
    .about-text .kicker { display: inline-block; margin-bottom: .6rem; }
    .about-text h2 { margin: 0 0 1.1rem; text-wrap: balance; }
    .about-text p { margin: 0 0 1rem; line-height: 1.75; text-wrap: pretty; overflow-wrap: anywhere; }
    .about-text .btn { margin-top: .6rem; }
    @media (max-width: 900px) {
        .about-grid { grid-template-columns: 1fr; }
        .about-media { margin: 0 16px 16px 0; }
    }
</style>

<section class="section about">
    <div class="wrap about-grid {{ filled($foundation->about_photo) ? '' : 'no-photo' }}">
        @if (filled($foundation->about_photo))
            <div class="about-media">
                <img src="{{ asset('storage/'.$foundation->about_photo) }}" alt="Tentang {{ $foundation->name }}" loading="lazy">
            </div>
        @endif

        <div class="about-text">
            <span class="kicker">Tentang Yayasan</span>
            <h2>Mengenal {{ $foundation->name }}</h2>
            @foreach (preg_split('/\R{2,}/', trim((string) $foundation->about_text)) as $para)
                @if (trim($para) !== '')
                    <p>{!! nl2br(e($para)) !!}</p>
                @endif
            @endforeach
            <a class="btn btn-outline" href="{{ route('portal.profile', 'sejarah') }}">Baca profil lengkap</a>
        </div>
    </div>
</section>
@endif

{{-- Visi & misi --}}
@if ($foundation->vision || $foundation->missionList())
<section class="section alt">
    <div class="wrap">
        <div class="sec-head">
            <span class="kicker">Arah Kami</span>
            <h2>Visi dan Misi</h2>
        </div>
        @include('partials.vm', ['entity' => $foundation])
        <div class="center" style="margin-top:1.8rem">
            <a class="btn btn-outline" href="{{ route('portal.profile', 'visi-misi') }}">Visi, misi, dan tujuan lengkap</a>
        </div>
    </div>
</section>
@endif

{{-- Jenjang --}}
<section class="section" id="jenjang">
    <div class="wrap">
        <div class="sec-head">
            <span class="kicker">Jenjang Pendidikan</span>
            <h2>Pendidikan Berkelanjutan dari PAUD hingga SMA</h2>
            <p>Pilih jenjang untuk melihat profil, program, dan informasi pendaftaran.</p>
        </div>
        <div class="cards cols-4">
            @foreach ($jenjang as $s)
                @if ($s->is_active)
                    <a class="card center portal-card" style="--primary: {{ $s->safeColor() }}" href="{{ route('home', $s) }}">
                        @if ($s->logoUrl())
                            <img class="logo-lg" src="{{ $s->logoUrl() }}" alt="">
                        @else
                            <div class="level-badge">{{ $s->levelLabel() }}</div>
                        @endif
                        <h3>{{ $s->name }}</h3>
                        <p class="muted small">Kunjungi situs →</p>
                    </a>
                @else
                    <div class="card center portal-card" style="--primary: {{ $s->safeColor() }}; opacity:.62">
                        <div class="level-badge">{{ $s->levelLabel() }}</div>
                        <h3>{{ $s->name }}</h3>
                        <p class="muted small">Segera hadir</p>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</section>

{{-- Pendiri (permanen di kode) dan Ketua Yayasan (dari database): partials/founder.blade.php --}}
@include('partials.founder', ['foundation' => $foundation])

{{-- SPMB + pengumuman --}}
@php $openSchools = $jenjang->filter(fn ($s) => $s->is_active && $s->ppdbIsOpen()); @endphp
<section class="section alt">
    <div class="wrap two-col">
        <div class="panel-box">
            <span class="kicker">SPMB</span>
            <h3>Penerimaan Siswa Baru</h3>
            @if ($openSchools->isNotEmpty())
                <p>Pendaftaran sedang dibuka untuk:</p>
                <div class="btns" style="margin-top:.6rem">
                    @foreach ($openSchools as $s)
                        <a class="btn btn-sm" href="{{ route('ppdb', $s) }}">Daftar {{ $s->name }}</a>
                    @endforeach
                </div>
            @else
                <p class="muted">Pendaftaran belum dibuka. Pantau terus halaman pengumuman.</p>
            @endif
            <p style="margin:1.2rem 0 0">
                <a class="more" href="{{ route('portal.spmb') }}">Informasi pendaftaran →</a>
            </p>
        </div>

        <div class="panel-box">
            <span class="kicker">Pengumuman</span>
            <h3>Pengumuman Terbaru</h3>
            @if ($announcements->isEmpty())
                <p class="muted">Belum ada pengumuman.</p>
            @else
                <ul class="notice-list">
                    @foreach ($announcements as $a)
                        <li>
                            <a href="{{ $a->publicUrl() }}">{{ $a->title }}</a>
                            <small>{{ $a->school->isFoundation() ? 'Yayasan' : $a->school->name }} · {{ $a->published_at?->translatedFormat('d F Y') }}</small>
                        </li>
                    @endforeach
                </ul>
            @endif
            <p style="margin:1rem 0 0"><a class="more" href="{{ route('portal.announcements') }}">Semua pengumuman →</a></p>
        </div>
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

{{-- Lokasi dan kontak --}}
@include('partials.location', ['entity' => $foundation])

@endsection