@extends('layouts.site')

@section('title', $school->name)

@section('content')

{{-- Hero: judul, deskripsi, tombol, dan sambutan kepala sekolah --}}
<section class="hero">
    <div class="wrap hero-grid">
        <div class="hero-text">
            <p class="eyebrow">{{ $school->levelLabel() }}</p>
            <h1>{{ $school->tagline ?: $school->name }}</h1>
            @if ($school->description)
                <p class="lead">{{ $school->description }}</p>
            @endif
            <div class="btns">
                <a class="btn" href="{{ route('profile', ['school' => $school, 'page' => 'sejarah']) }}">Lihat Profil</a>
                <a class="btn btn-outline" href="{{ route('ppdb', $school) }}">Pendaftaran Siswa</a>
            </div>
        </div>

        <figure class="head-card">
            @if ($school->headPhotoUrl())
                <img src="{{ $school->headPhotoUrl() }}" alt="{{ $school->head_title }} {{ $school->head_name }}">
            @else
                <div class="avatar" aria-hidden="true">{{ mb_substr((string) $school->head_name ?: $school->name, 0, 1) }}</div>
            @endif
            <figcaption>
                @if ($school->welcome_message)
                    <blockquote>{{ $school->welcome_message }}</blockquote>
                @endif
                <strong>{{ $school->head_name }}</strong>
                <span>{{ $school->head_title }}</span>
            </figcaption>
        </figure>
    </div>
</section>

{{-- Visi & misi ringkas --}}
@if ($school->vision || $school->missionList())
<section class="section">
    <div class="wrap two-col">
        <div>
            <h2>Visi</h2>
            <p class="lead">{{ $school->vision }}</p>
        </div>
        <div>
            <h2>Misi</h2>
            <ul class="check">
                @foreach ($school->missionList() as $m)
                    <li>{{ $m }}</li>
                @endforeach
            </ul>
            <a class="btn btn-outline" href="{{ route('profile', ['school' => $school, 'page' => 'visi-misi']) }}">Visi dan misi lengkap</a>
        </div>
    </div>
</section>
@endif

{{-- Keunggulan --}}
@if ($advantages->isNotEmpty())
<section class="section alt">
    <div class="wrap">
        <h2 class="center">Mengapa Anak Anda Harus Memilih Sekolah Kami?</h2>
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

{{-- Berita & pengumuman terbaru --}}
<section class="section">
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
<section class="section alt">
    <div class="wrap">
        <div class="section-head">
            <h2>Galeri Kegiatan</h2>
            <a href="{{ route('gallery', $school) }}">Lihat semua →</a>
        </div>
        <div class="photo-grid">
            @foreach ($photos as $p)
                <figure>
                    <img src="{{ asset('storage/'.$p->image) }}" alt="{{ $p->title }}" loading="lazy">
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
        <h2 class="center">Pertanyaan yang Sering Diajukan</h2>
        @foreach ($faqs as $f)
            <details class="faq">
                <summary>{{ $f->question }}</summary>
                <p>{!! nl2br(e($f->answer)) !!}</p>
            </details>
        @endforeach
    </div>
</section>
@endif

@endsection