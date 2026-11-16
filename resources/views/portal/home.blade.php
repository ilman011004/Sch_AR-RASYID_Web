@extends('layouts.portal')

@section('title', $foundation->name)
@section('body_class', 'hero-page')

@section('content')

@php
    $activeSchools = $jenjang->where('is_active', true);
    $openSchools = $jenjang->filter(fn ($s) => $s->is_active && $s->ppdbIsOpen());

    // Foto bagian "Tentang Yayasan": dikelola di menu Foto Tentang Yayasan (tampil sebagai slider, maks. 10)
    $aboutPhotos = $foundation->aboutPhotos->take(10)->values();
    $photoCount = $aboutPhotos->count();
@endphp

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
            <span class="eyebrow">Yayasan Pendidikan</span>
            <h1 style="font-size: {{ $size }}; text-wrap: balance;">{!! $titleHtml !!}</h1>
            @if ($foundation->description)
                <p class="lead">{{ $foundation->description }}</p>
            @endif
            <div class="btns">
                <a class="btn hero-btn-spmb" href="{{ route('portal.spmb') }}">Info SPMB</a>
                <a class="btn hero-btn-about" href="{{ route('portal.profile', 'sejarah') }}">Tentang Yayasan</a>
            </div>
        </div>

        @include('partials.hero-slider', ['slides' => $slides])
    </div>
</section>

{{-- Akses cepat (ikon SVG, bukan emoji) --}}
<div class="quick">
    <div class="wrap quick-grid" data-stagger>
        <a href="{{ route('portal.spmb') }}">
            <span class="qi">@include('partials.icon', ['name' => 'graduation'])</span>
            <span><b>Info SPMB</b><small>Pendaftaran siswa baru</small></span>
            <span class="arrow">@include('partials.icon', ['name' => 'arrow-right'])</span>
        </a>
        <a href="#jenjang">
            <span class="qi">@include('partials.icon', ['name' => 'school'])</span>
            <span><b>Jenjang Pendidikan</b><small>PAUD sampai SMA</small></span>
            <span class="arrow">@include('partials.icon', ['name' => 'arrow-right'])</span>
        </a>
        <a href="{{ route('portal.profile', 'visi-misi') }}">
            <span class="qi">@include('partials.icon', ['name' => 'star'])</span>
            <span><b>Visi dan Misi</b><small>Arah yayasan</small></span>
            <span class="arrow">@include('partials.icon', ['name' => 'arrow-right'])</span>
        </a>
        <a href="{{ route('portal.news') }}">
            <span class="qi">@include('partials.icon', ['name' => 'mail'])</span>
            <span><b>Berita</b><small>Kabar terbaru</small></span>
            <span class="arrow">@include('partials.icon', ['name' => 'arrow-right'])</span>
        </a>
    </div>
</div>

{{-- Sambutan ketua kini tampil di bagian Pendiri dan Ketua (lebih bawah). Untuk menampilkan sambutan lengkap lagi, aktifkan baris berikut:
@include('partials.welcome', ['entity' => $foundation])
--}}

{{-- ================= Tentang Yayasan ================= --}}
<style>
    /* Hero button styling */
    .hero-btn-spmb {
        background: var(--accent, #22a06b) !important;
        border-color: var(--accent, #22a06b) !important;
        color: #fff !important;
        font-weight: 700 !important;
        box-shadow: 0 8px 20px rgba(0, 0, 0, .2) !important;
        transition: all .25s ease !important;
    }
    .hero-btn-spmb:hover {
        background: color-mix(in srgb, var(--accent, #22a06b) 85%, #000) !important;
        border-color: color-mix(in srgb, var(--accent, #22a06b) 85%, #000) !important;
        color: #fff !important;
        transform: translateY(-2px);
        box-shadow: 0 12px 28px rgba(0, 0, 0, .28) !important;
    }

    /* Button Tentang Yayasan: sebelum diarahkan transparan bergaris putih, pas diarahkan kursor jadi biru tulisan putih */
    .hero-btn-about {
        background: transparent !important;
        border: 2px solid rgba(255, 255, 255, .85) !important;
        color: #fff !important;
        font-weight: 700 !important;
        transition: all .25s ease !important;
    }
    .hero-btn-about:hover {
        background: var(--primary, #0f766e) !important;
        border-color: var(--primary, #0f766e) !important;
        color: #fff !important;
        transform: translateY(-2px);
        box-shadow: 0 10px 24px rgba(0, 0, 0, .25) !important;
    }

    /* Section Tentang Yayasan */
    .about {
        position: relative;
        background: #fff;
        overflow: hidden;
        padding: clamp(3.5rem, 5vw, 5rem) 0;
    }
    .about::before {
        content: ""; position: absolute; pointer-events: none;
        width: 560px; height: 560px; right: -160px; top: -140px; border-radius: 50%;
        background: radial-gradient(closest-side, color-mix(in srgb, var(--primary) 8%, transparent), transparent);
    }
    .about > .wrap { position: relative; z-index: 2; }

    /* Dua kolom rapi dan proporsional */
    .about-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.1fr) minmax(0, 1fr);
        gap: clamp(2.5rem, 5vw, 4rem);
        align-items: center;
    }
    .about-grid.no-media { grid-template-columns: minmax(0, 1fr); max-width: 800px; margin-inline: auto; }

    /* ---- Teks Kiri ---- */
    .about-text { min-width: 0; }
    
    .about-kicker {
        display: inline-block;
        font-size: .85rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: var(--accent, #22a06b);
        margin-bottom: .6rem;
    }

    .about-text h2 {
        margin: 0 0 1.2rem;
        font-size: clamp(1.85rem, 2.8vw, 2.5rem);
        font-weight: 800;
        line-height: 1.25;
        letter-spacing: -.02em;
        color: #0f172a;
        word-break: keep-all;
        overflow-wrap: normal;
        white-space: normal;
    }
    .about-text h2::after {
        content: ""; display: block; width: 60px; height: 4px; border-radius: 4px;
        margin-top: .9rem; background: var(--accent, #22a06b);
    }

    /* Deskripsi: text-break aman agar teks panjang/URL tidak meluap */
    .about-desc {
        margin-bottom: 1.8rem;
        padding-left: 1.25rem;
        border-left: 3px solid color-mix(in srgb, var(--accent, #22a06b) 45%, #fff);
    }
    .about-desc p {
        margin: 0 0 1.15rem;
        font-size: 1.05rem;
        font-weight: 400;
        line-height: 1.85;
        color: #334155;
        text-align: left;
        overflow-wrap: break-word;
        word-break: break-word;
    }
    .about-desc p:last-child { margin-bottom: 0; }

    /* Tombol Aksi */
    .about-cta { display: flex; align-items: center; flex-wrap: wrap; gap: 1rem 1.6rem; }
    .about-cta .btn {
        background: var(--accent, #22a06b);
        border: none;
        color: #fff;
        font-weight: 600;
        padding: .8rem 1.6rem;
        border-radius: 12px;
        box-shadow: 0 10px 22px -8px color-mix(in srgb, var(--accent, #22a06b) 70%, transparent);
        transition: transform .2s, box-shadow .2s;
    }
    .about-cta .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 14px 26px -8px color-mix(in srgb, var(--accent, #22a06b) 85%, transparent);
    }
    .about-link {
        font-weight: 700;
        color: #0f172a;
        text-decoration: none;
        padding-bottom: 2px;
        border-bottom: 2px solid var(--accent-2, #f5b800);
        transition: color .2s, border-color .2s;
    }
    .about-link:hover { color: var(--accent, #22a06b); border-color: var(--accent, #22a06b); }

    /* ---- Kolom Kanan: Slider Foto Utuh ---- */
    .about-slider { position: relative; min-width: 0; }
    .as-stage {
        position: relative;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 24px 50px -15px rgba(15, 23, 42, .25);
    }
    .as-viewport {
        position: relative; display: flex; overflow-x: auto; overscroll-behavior-x: contain;
        scroll-snap-type: x mandatory; scrollbar-width: none; -ms-overflow-style: none;
        aspect-ratio: 16 / 10.5; background: #0b141e;
    }
    .as-viewport::-webkit-scrollbar { display: none; }
    .as-viewport:focus-visible { outline: 3px solid var(--accent-2, #f5b800); outline-offset: 3px; }
    .as-slide {
        position: relative; flex: 0 0 100%; height: 100%; margin: 0; overflow: hidden;
        scroll-snap-align: center; scroll-snap-stop: always;
    }
    .as-bg {
        position: absolute; inset: -15%; background-size: cover; background-position: center;
        filter: blur(28px) brightness(.75) saturate(1.15);
    }
    .as-slide img { position: relative; z-index: 1; display: block; width: 100%; height: 100%; object-fit: contain; }
    .as-cap {
        position: absolute; z-index: 2; left: 0; right: 0; bottom: 0; padding: 2.2rem 1.4rem .9rem;
        color: #fff; font-size: .92rem; font-weight: 600; line-height: 1.35;
        background: linear-gradient(to top, rgba(8, 16, 28, .85), transparent);
        display: flex; align-items: center; gap: .5rem;
    }
    .as-cap::before {
        content: ""; width: 8px; height: 8px; border-radius: 50%;
        background: var(--accent-2, #f5b800); flex-shrink: 0;
    }

    .as-btn {
        position: absolute; z-index: 3; top: 50%; transform: translateY(-50%);
        width: 44px; height: 44px; border: 0; border-radius: 50%; cursor: pointer;
        background: rgba(255, 255, 255, .95); color: #0f172a; font-size: 1.4rem; line-height: 1;
        display: grid; place-items: center; box-shadow: 0 10px 25px rgba(0, 0, 0, .28);
        transition: transform .2s, background .2s, color .2s;
    }
    .as-btn:hover { background: #fff; color: var(--accent, #22a06b); transform: translateY(-50%) scale(1.08); }
    .as-btn:focus-visible { outline: 3px solid var(--accent-2, #f5b800); outline-offset: 2px; }
    .as-prev { left: 1rem; }
    .as-next { right: 1rem; }
    .as-dots { display: flex; justify-content: center; flex-wrap: wrap; gap: .55rem; margin-top: 1.1rem; }
    .as-dot {
        width: 10px; height: 10px; padding: 0; border: 0; border-radius: 999px; cursor: pointer;
        background: #cbd5e1; transition: width .3s, background .3s;
    }
    .as-dot.is-active { width: 28px; background: var(--accent, #22a06b); }

    /* Cadangan Logo */
    .about-logo {
        display: grid; place-items: center; aspect-ratio: 16 / 10.5; border-radius: 20px; padding: 12%;
        background: linear-gradient(135deg, color-mix(in srgb, var(--primary) 75%, #000), var(--primary));
        box-shadow: 0 30px 56px -30px rgba(16, 24, 40, .4);
    }
    .about-logo img { max-width: 100%; max-height: 100%; object-fit: contain; filter: drop-shadow(0 12px 24px rgba(0, 0, 0, .3)); }

    @media (max-width: 900px) {
        .about-grid { grid-template-columns: 1fr; }
        .about-text h2 { font-size: clamp(1.8rem, 5vw, 2.3rem); }
    }
    @media (max-width: 560px) {
        .as-btn { width: 36px; height: 36px; font-size: 1.15rem; }
        .about-desc { padding-left: 1rem; }
        .about-desc p { font-size: 1rem; line-height: 1.75; }
    }
</style>

@if (filled($foundation->about_text) || $photoCount > 0)
<section class="section about" id="tentang">
    <div class="wrap">
        <div class="about-grid {{ ($photoCount > 0 || $foundation->logoUrl()) ? '' : 'no-media' }}">

            <div class="about-text" data-reveal>
                <span class="about-kicker">Tentang Yayasan</span>
                <h2>Mengenal <span style="white-space: nowrap;">{{ $foundation->name }}</span></h2>

                <div class="about-desc">
                    @foreach (preg_split('/\R{2,}/', trim((string) $foundation->about_text)) as $para)
                        @if (trim($para) !== '')
                            <p>{!! nl2br(e($para)) !!}</p>
                        @endif
                    @endforeach
                </div>

                <div class="about-cta">
                    <a class="btn" href="{{ route('portal.profile', 'sejarah') }}">
                        Baca profil lengkap
                    </a>
                    <a class="about-link" href="{{ route('portal.profile', 'visi-misi') }}">
                        Visi dan misi →
                    </a>
                </div>
            </div>

            @if ($photoCount > 0)
                <div class="about-slider" data-about-slider data-reveal role="region"
                     aria-roledescription="carousel" aria-label="Foto Tentang Yayasan">
                    <div class="as-stage">
                        <div class="as-viewport" tabindex="0">
                            @foreach ($aboutPhotos as $photo)
                                <figure class="as-slide" aria-label="Foto {{ $loop->iteration }} dari {{ $photoCount }}">
                                    <div class="as-bg" style="background-image: url('{{ $photo->imageUrl() }}')" aria-hidden="true"></div>
                                    <img src="{{ $photo->imageUrl() }}" alt="{{ $photo->title }}" @if (! $loop->first) loading="lazy" @endif>
                                    @if (filled($photo->title))
                                        <figcaption class="as-cap">{{ $photo->title }}</figcaption>
                                    @endif
                                </figure>
                            @endforeach
                        </div>

                        @if ($photoCount > 1)
                            <button type="button" class="as-btn as-prev" aria-label="Foto sebelumnya">‹</button>
                            <button type="button" class="as-btn as-next" aria-label="Foto berikutnya">›</button>
                        @endif
                    </div>

                    @if ($photoCount > 1)
                        <div class="as-dots">
                            @foreach ($aboutPhotos as $photo)
                                <button type="button" class="as-dot {{ $loop->first ? 'is-active' : '' }}" aria-label="Ke foto {{ $loop->iteration }}"></button>
                            @endforeach
                        </div>
                    @endif
                </div>
            @elseif ($foundation->logoUrl())
                <div class="about-logo" data-reveal>
                    <img src="{{ $foundation->logoUrl() }}" alt="Logo {{ $foundation->name }}" loading="lazy">
                </div>
            @endif

        </div>
    </div>
</section>
@endif

{{-- Pendiri (permanen di kode) dan Ketua Yayasan (dari database): partials/founder.blade.php --}}
@include('partials.founder', ['foundation' => $foundation])

{{-- Pita angka (dihitung dari data nyata, bukan angka karangan) --}}
@if ($jenjang->count() > 0)
<section class="section stats figures" style="padding: clamp(2.6rem, 6vw, 4rem) 0">
    <div class="wrap stat-grid" data-stagger>
        <div class="stat">
            <div class="num">{{ $jenjang->count() }}</div>
            <div>Jenjang pendidikan</div>
        </div>
        <div class="stat">
            <div class="num">{{ $activeSchools->count() }}</div>
            <div>Situs sekolah aktif</div>
        </div>
        <div class="stat">
            <div class="num">{{ $openSchools->count() }}</div>
            <div>Pendaftaran sedang dibuka</div>
        </div>
    </div>
</section>
@endif

{{-- Jenjang --}}
<section class="section" id="jenjang">
    <div class="wrap">
        <div class="sec-head" data-reveal>
            <span class="kicker">Jenjang Pendidikan</span>
            <h2>Pendidikan Berkelanjutan dari PAUD hingga SMA</h2>
            <p>Pilih jenjang untuk melihat profil, program, dan informasi pendaftaran.</p>
        </div>
        <div class="cards cols-4" data-stagger>
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

{{-- SPMB + pengumuman --}}
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
        <div class="section-head" data-reveal>
            <h2>Berita dan Informasi Terbaru</h2>
            <a href="{{ route('portal.news') }}">Lihat semua →</a>
        </div>

        @if ($news->isEmpty())
            <p class="muted">Belum ada berita.</p>
        @else
            <div class="cards cols-3" data-stagger>
                @foreach ($news as $post)
                    @include('portal._post-card', ['post' => $post])
                @endforeach
            </div>
        @endif
    </div>
</section>

{{-- Lokasi dan kontak --}}
@include('partials.location', ['entity' => $foundation])

{{-- Animasi muncul saat di-scroll (site-polish.css menyembunyikan [data-reveal]/[data-stagger] sampai kelas is-in dipasang) --}}
<script>
    (function () {
        var els = document.querySelectorAll('[data-reveal], [data-stagger]');
        if (!els.length) return;
        if (!('IntersectionObserver' in window)) {
            els.forEach(function (el) { el.classList.add('is-in'); });
            return;
        }
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (e) {
                if (e.isIntersecting) { e.target.classList.add('is-in'); io.unobserve(e.target); }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });
        els.forEach(function (el) { io.observe(el); });
    })();

    // Slider foto Tentang Yayasan
    (function () {
        var root = document.querySelector('[data-about-slider]');
        if (!root) return;

        var vp = root.querySelector('.as-viewport');
        var n = root.querySelectorAll('.as-slide').length;
        var dots = root.querySelectorAll('.as-dot');
        var prev = root.querySelector('.as-prev');
        var next = root.querySelector('.as-next');
        if (n < 2) return;

        var cur = 0, timer = null, ticking = false;
        var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        function mark() {
            dots.forEach(function (d, i) { d.classList.toggle('is-active', i === cur); });
        }
        function go(i) {
            var wrap = i < 0 || i >= n;                       // dari akhir ke awal: lompat, bukan menggulung
            cur = (i + n) % n;
            vp.scrollTo({ left: cur * vp.clientWidth, behavior: wrap ? 'auto' : 'smooth' });
            mark();
        }
        function stop() { if (timer) { clearInterval(timer); timer = null; } }
        function play() {
            stop();
            if (!reduce) timer = setInterval(function () { go(cur + 1); }, 5000);
        }

        prev.addEventListener('click', function () { go(cur - 1); });
        next.addEventListener('click', function () { go(cur + 1); });
        dots.forEach(function (d, i) { d.addEventListener('click', function () { go(i); }); });

        // Ikuti geseran jari/mouse supaya titik penanda selalu sesuai
        vp.addEventListener('scroll', function () {
            if (ticking) return;
            ticking = true;
            requestAnimationFrame(function () {
                ticking = false;
                var i = Math.round(vp.scrollLeft / vp.clientWidth);
                if (i !== cur && i >= 0 && i < n) { cur = i; mark(); }
            });
        }, { passive: true });

        vp.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowLeft') { e.preventDefault(); go(cur - 1); }
            if (e.key === 'ArrowRight') { e.preventDefault(); go(cur + 1); }
        });

        window.addEventListener('resize', function () {
            vp.scrollTo({ left: cur * vp.clientWidth, behavior: 'auto' });
        });

        // Geser otomatis; berhenti saat disentuh/disorot, dan saat slider tidak terlihat
        ['mouseenter', 'focusin', 'touchstart', 'pointerdown'].forEach(function (ev) {
            root.addEventListener(ev, stop, { passive: true });
        });
        ['mouseleave', 'focusout', 'touchend'].forEach(function (ev) {
            root.addEventListener(ev, play);
        });
        if ('IntersectionObserver' in window) {
            new IntersectionObserver(function (entries) {
                entries[0].isIntersecting ? play() : stop();
            }, { threshold: 0.25 }).observe(root);
        } else {
            play();
        }
    })();
</script>

@endsection