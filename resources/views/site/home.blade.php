@extends('layouts.site')

@section('title', $school->name)
@section('body_class', 'hero-page')

@section('content')

@php
    // ID video YouTube dari link yang diisi di Pengaturan Sekolah (kosong/tidak valid = null)
    $ytId = null;
    if (filled($school->video_url) && preg_match('~(?:youtu\.be/|youtube(?:-nocookie)?\.com/(?:watch\?(?:[^#]*&)?v=|embed/|shorts/|live/))([\w-]{11})~', $school->video_url, $m)) {
        $ytId = $m[1];
    }

    // Kata ber-tanda hubung (Ar-Rasyid, Nilai-Nilai) dijaga utuh supaya tidak terpotong "Ar–" / "Rasyid"
    $nw = fn ($t) => preg_replace_callback('/\S*[-–]\S*/u', fn ($m) => '<span class="nw">'.$m[0].'</span>', e($t));
@endphp

<style>
    .hero .hero-text h1 {
        font-size: clamp(2rem, 1.2rem + 3.6vw, 4rem);
        line-height: 1.1;
        text-wrap: balance;        /* baris dibagi rata, tidak ada baris yatim */
        overflow-wrap: break-word; /* pengaman kalau ada satu kata yang sangat panjang */
    }
    .hero .hero-text .lead {
        font-size: clamp(1rem, .9rem + .5vw, 1.25rem);
        line-height: 1.6;
        text-wrap: pretty;         /* mencegah satu kata sendirian di baris terakhir */
        max-width: 40em;
    }
    .hero .hero-text .nw { white-space: nowrap; }
</style>

{{-- Hero: teks + slider foto --}}
<section class="hero">
    <div class="wrap hero-grid {{ $slides->isEmpty() ? 'no-slider' : '' }}">
        <div class="hero-text">
            @if ($school->ppdbIsOpen())
                <span class="pill">● Pendaftaran siswa baru dibuka</span>
            @endif
            <h1>{!! $nw($school->tagline ?: $school->name) !!}</h1>
            @if ($school->description)
                <p class="lead">{!! $nw($school->description) !!}</p>
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
    <div class="wrap quick-grid" data-stagger>
        <a href="{{ route('ppdb', $school) }}">
            <span class="qi">@include('partials.icon', ['name' => 'graduation'])</span>
            <span><b>Pendaftaran Siswa</b><small>{{ $school->ppdbIsOpen() ? 'Sedang dibuka' : 'Lihat informasi' }}</small></span>
            <span class="arrow">@include('partials.icon', ['name' => 'arrow-right'])</span>
        </a>
        <a href="{{ route('profile', ['school' => $school, 'page' => 'visi-misi']) }}">
            <span class="qi">@include('partials.icon', ['name' => 'school'])</span>
            <span><b>Visi dan Misi</b><small>Kenali sekolah kami</small></span>
            <span class="arrow">@include('partials.icon', ['name' => 'arrow-right'])</span>
        </a>
        <a href="{{ route('programs', ['school' => $school, 'page' => 'unggulan']) }}">
            <span class="qi">@include('partials.icon', ['name' => 'star'])</span>
            <span><b>Program Unggulan</b><small>Program akademik</small></span>
            <span class="arrow">@include('partials.icon', ['name' => 'arrow-right'])</span>
        </a>
        <a href="{{ route('contact', $school) }}">
            <span class="qi">@include('partials.icon', ['name' => 'mail'])</span>
            <span><b>Hubungi Kami</b><small>Tanya apa saja</small></span>
            <span class="arrow">@include('partials.icon', ['name' => 'arrow-right'])</span>
        </a>
    </div>
</div>

{{-- Sambutan kepala sekolah: video bila ada, foto bila belum. Angka statistik selalu tampil di sini --}}
@include('partials.welcome-video', ['entity' => $school, 'ytId' => $ytId, 'stats' => $stats])

{{-- Visi & misi --}}
@if ($school->vision || $school->missionList())
<section class="section alt">
    <div class="wrap">
        <div class="sec-head" data-reveal>
            <span class="kicker">Tentang Kami</span>
            <h2>Visi dan Misi</h2>
        </div>
        <div data-reveal>
            @include('partials.vm', ['entity' => $school])
        </div>
        <div class="center" style="margin-top:1.8rem" data-reveal>
            <a class="btn btn-outline" href="{{ route('profile', ['school' => $school, 'page' => 'visi-misi']) }}">Visi, misi, dan tujuan lengkap</a>
        </div>
    </div>
</section>
@endif

{{-- Keunggulan: kolom, tinggi kartu, dan baris judul menyesuaikan isi secara otomatis --}}
@if ($advantages->isNotEmpty())
<style>
    /* Judul dua baris: "Mengapa Memilih" di atas, nama sekolah (tiap jenjang) di bawahnya */
    .adv .sec-head h2 { text-wrap: balance; }
    .adv .sec-head h2 .adv-name { display: block; margin-top: .15em; text-wrap: balance; }

    /* Jumlah kolom menyesuaikan lebar layar dan jumlah kartu */
    .cards.adv-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(min(100%, 230px), 1fr));
        gap: 0 1.2rem;
        align-items: stretch;
    }
    .adv-grid .card {
        margin-bottom: 1.2rem;
        height: auto;
        overflow-wrap: anywhere;
        hyphens: auto;
    }
    .adv-grid .card h3 { text-wrap: balance; margin: 0; }
    .adv-grid .card p  { text-wrap: pretty; margin: 0; }

    /* Ikon, judul, dan deskripsi tiap kartu sejajar dengan kartu lain,
       dan tinggi semua kartu otomatis sama mengikuti isi terpanjang */
    @supports (grid-template-rows: subgrid) {
        .adv-grid .card {
            display: grid;
            grid-row: span 3;
            grid-template-rows: subgrid;
            row-gap: .7rem;
            align-content: start;
        }
        .adv-grid .card .icon { grid-row: 1; justify-self: center; }
        .adv-grid .card h3    { grid-row: 2; }
        .adv-grid .card p     { grid-row: 3; }
    }
    @supports not (grid-template-rows: subgrid) {
        .adv-grid .card { display: flex; flex-direction: column; align-items: center; gap: .7rem; }
    }
</style>

<section class="section adv">
    <div class="wrap">
        <div class="sec-head" data-reveal>
            <span class="kicker">Keunggulan</span>
            <h2>Mengapa Memilih <span class="adv-name">{{ $school->name }}?</span></h2>
        </div>
        <div class="cards adv-grid" data-stagger>
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

{{-- Fasilitas (hanya tampil bila sudah ada datanya) --}}
@include('partials.facilities', ['facilities' => $school->facilities])

{{-- Berita --}}
<section class="section">
    <div class="wrap">
        <div class="section-head" data-reveal>
            <h2>Berita dan Informasi Terbaru</h2>
            <a href="{{ route('news', $school) }}">Lihat semua →</a>
        </div>

        @if ($news->isEmpty())
            <p class="muted">Belum ada berita.</p>
        @else
            <div class="cards cols-3" data-stagger>
                @foreach ($news as $post)
                    @include('site._post-card', ['post' => $post])
                @endforeach
            </div>
        @endif
    </div>
</section>

{{-- Galeri --}}
@if ($photos->isNotEmpty())
<section class="section alt">
    <div class="wrap">
        <div class="section-head" data-reveal>
            <h2>Galeri Kegiatan</h2>
            <a href="{{ route('gallery', $school) }}">Lihat semua →</a>
        </div>
        <div class="photo-grid" data-stagger>
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

{{-- FAQ --}}
@if ($faqs->isNotEmpty())
<section class="section">
    <div class="wrap narrow">
        <div class="sec-head" data-reveal>
            <span class="kicker">FAQ</span>
            <h2>Pertanyaan yang Sering Diajukan</h2>
        </div>
        <div data-stagger>
            @foreach ($faqs as $f)
                <details class="faq">
                    <summary>{{ $f->question }}</summary>
                    <p>{!! nl2br(e($f->answer)) !!}</p>
                </details>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- Lokasi dan kontak --}}
<div data-reveal>
    @include('partials.location', ['entity' => $school, 'contactUrl' => route('contact', $school)])
</div>

<script>
    (function () {
        var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        var targets = [].slice.call(document.querySelectorAll('[data-reveal], [data-stagger]'));

        // Tanpa dukungan IntersectionObserver atau gerak dikurangi: tampilkan semuanya langsung
        if (!('IntersectionObserver' in window) || reduce) {
            targets.forEach(function (el) { el.classList.add('is-in'); });
            return;
        }

        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (e) {
                if (!e.isIntersecting) return;
                e.target.classList.add('is-in');
                io.unobserve(e.target);
                e.target.querySelectorAll('[data-count]').forEach(countUp);
            });
        }, { threshold: 0.15, rootMargin: '0px 0px -8% 0px' });

        targets.forEach(function (el) { io.observe(el); });

        function countUp(el) {
            var end = parseInt(el.getAttribute('data-count'), 10);
            if (isNaN(end) || end < 1) return;
            var start = null, dur = 1400;
            function step(t) {
                if (start === null) start = t;
                var p = Math.min((t - start) / dur, 1);
                var eased = 1 - Math.pow(1 - p, 3);
                el.textContent = Math.round(end * eased).toLocaleString('id-ID');
                if (p < 1) requestAnimationFrame(step);
            }
            el.textContent = '0';
            requestAnimationFrame(step);
        }
    })();
</script>

@endsection