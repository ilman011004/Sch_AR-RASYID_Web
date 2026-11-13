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
                <a class="btn" href="{{ route('portal.spmb') }}">Info SPMB</a>
                <a class="btn btn-outline" href="{{ route('portal.profile', 'sejarah') }}">Tentang Yayasan</a>
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
    .about { position: relative; background: #fff; overflow: hidden; }
    .about::before {
        content: ""; position: absolute; pointer-events: none;
        width: 560px; height: 560px; right: -180px; top: -160px; border-radius: 50%;
        background: radial-gradient(closest-side, color-mix(in srgb, var(--primary) 9%, transparent), transparent);
    }
    .about > .wrap { position: relative; z-index: 1; }

    /* Dua kolom sama lebar, sejajar di tengah secara vertikal */
    .about-grid {
        display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1.05fr);
        gap: clamp(2rem, 4.5vw, 4rem); align-items: center;
    }
    .about-grid.no-media { grid-template-columns: minmax(0, 1fr); max-width: 760px; margin-inline: auto; }

    /* ---- Teks ---- */
    .about-text { min-width: 0; }
    .about-text .kicker { display: inline-block; margin-bottom: .6rem; }
    .about-text h2 {
        margin: 0 0 1.5rem; font-size: clamp(1.9rem, 3.2vw, 2.7rem);
        line-height: 1.15; letter-spacing: -.02em; text-wrap: balance;
    }
    /* Garis aksen di bawah judul */
    .about-text h2::after {
        content: ""; display: block; width: 56px; height: 4px; border-radius: 4px;
        margin-top: 1rem; background: var(--accent, #22a06b);
    }
    /* Deskripsi: semua paragraf sama (warna, ukuran, ketebalan), dikelompokkan dengan garis aksen di kiri */
    .about-desc {
        max-width: 60ch; padding-left: 1.3rem;
        border-left: 3px solid color-mix(in srgb, var(--accent, #22a06b) 45%, #fff);
    }
    .about-desc p {
        margin: 0 0 1.15rem; font-size: 1.02rem; font-weight: 400; line-height: 1.85;
        color: var(--text); text-align: left; text-wrap: pretty; overflow-wrap: anywhere;
    }
    .about-desc p:last-child { margin-bottom: 0; }

    /* Tombol dan tautan */
    .about-cta { display: flex; align-items: center; flex-wrap: wrap; gap: .9rem 1.6rem; margin-top: 1.8rem; }
    .about-cta .btn {
        background: var(--accent, #22a06b); border-color: var(--accent, #22a06b); color: #fff;
        opacity: 1; box-shadow: 0 10px 22px -10px color-mix(in srgb, var(--accent, #22a06b) 70%, transparent);
    }
    .about-cta .btn:hover { background: color-mix(in srgb, var(--accent, #22a06b) 85%, #000); border-color: color-mix(in srgb, var(--accent, #22a06b) 85%, #000); }
    .about-link {
        font-weight: 700; color: var(--ink); text-decoration: none; padding-bottom: 2px;
        border-bottom: 2px solid color-mix(in srgb, var(--accent-2, #f5b800) 80%, transparent);
        transition: border-color .2s, color .2s;
    }
    .about-link:hover { color: var(--accent, #22a06b); border-bottom-color: var(--accent-2, #f5b800); }

    /* ---- Slider foto: foto tampil utuh (tidak terpotong), geser sendiri, panah kiri-kanan ---- */
    .about-slider { position: relative; min-width: 0; }
    .as-stage { position: relative; }
    .as-viewport {
        position: relative; display: flex; overflow-x: auto; overscroll-behavior-x: contain;
        scroll-snap-type: x mandatory; scrollbar-width: none; -ms-overflow-style: none;
        aspect-ratio: 16 / 10; border-radius: 20px; background: #0e1b27;
        box-shadow: 0 30px 56px -30px rgba(16, 24, 40, .55);
    }
    .as-viewport::-webkit-scrollbar { display: none; }
    .as-viewport:focus-visible { outline: 3px solid var(--accent-2, #f5b800); outline-offset: 3px; }
    .as-slide {
        position: relative; flex: 0 0 100%; height: 100%; margin: 0; overflow: hidden;
        scroll-snap-align: center; scroll-snap-stop: always;
    }
    /* Latar buram dari foto yang sama, mengisi ruang kosong di samping foto */
    .as-bg {
        position: absolute; inset: -12%; background-size: cover; background-position: center;
        filter: blur(28px) brightness(.85) saturate(1.1);
    }
    .as-slide img { position: relative; z-index: 1; display: block; width: 100%; height: 100%; object-fit: contain; }
    .as-cap {
        position: absolute; z-index: 2; left: 0; right: 0; bottom: 0; padding: 2.2rem 1.2rem .85rem;
        color: #fff; font-size: .88rem; font-weight: 600; line-height: 1.35;
        background: linear-gradient(to top, rgba(8, 16, 28, .72), transparent);
    }
    .as-btn {
        position: absolute; z-index: 3; top: 50%; transform: translateY(-50%);
        width: 42px; height: 42px; border: 0; border-radius: 50%; cursor: pointer;
        background: rgba(255, 255, 255, .95); color: #0b1620; font-size: 1.5rem; line-height: 1;
        display: grid; place-items: center; box-shadow: 0 10px 26px rgba(0, 0, 0, .28);
        transition: transform .2s, background .2s;
    }
    .as-btn:hover { background: #fff; transform: translateY(-50%) scale(1.07); }
    .as-btn:focus-visible { outline: 3px solid var(--accent-2, #f5b800); outline-offset: 2px; }
    .as-prev { left: .8rem; }
    .as-next { right: .8rem; }
    .as-dots { display: flex; justify-content: center; flex-wrap: wrap; gap: .5rem; margin-top: 1rem; }
    .as-dot {
        width: 9px; height: 9px; padding: 0; border: 0; border-radius: 999px; cursor: pointer;
        background: color-mix(in srgb, var(--ink) 22%, transparent); transition: width .3s, background .3s;
    }
    .as-dot.is-active { width: 26px; background: var(--accent, #22a06b); }

    /* Cadangan bila belum ada foto: logo yayasan */
    .about-logo {
        display: grid; place-items: center; aspect-ratio: 16 / 10; border-radius: 20px; padding: 12%;
        background: linear-gradient(135deg, color-mix(in srgb, var(--primary) 68%, #000), var(--primary));
        box-shadow: 0 30px 56px -30px rgba(16, 24, 40, .55);
    }
    .about-logo img { max-width: 100%; max-height: 100%; object-fit: contain; filter: drop-shadow(0 12px 24px rgba(0, 0, 0, .25)); }

    /* Pita angka */
    .figures .stat .num { font-variant-numeric: tabular-nums; }

    @media (max-width: 900px) {
        .about-grid { grid-template-columns: 1fr; }
        .about-text h2 { font-size: clamp(1.7rem, 6vw, 2.2rem); }
    }
    @media (max-width: 560px) {
        .as-btn { width: 36px; height: 36px; font-size: 1.25rem; }
        .as-viewport, .about-logo { border-radius: 16px; }
        .about-desc { padding-left: 1rem; }
        .about-desc p { font-size: 1rem; line-height: 1.75; }
    }
</style>

@if (filled($foundation->about_text) || $photoCount > 0)
<section class="section about" id="tentang">
    <div class="wrap about-grid {{ ($photoCount > 0 || $foundation->logoUrl()) ? '' : 'no-media' }}">

        <div class="about-text" data-reveal>
            <span class="kicker">Tentang Yayasan</span>
            <h2>Mengenal {{ $foundation->name }}</h2>

            <div class="about-desc">
                @foreach (preg_split('/\R{2,}/', trim((string) $foundation->about_text)) as $para)
                    @if (trim($para) !== '')
                        <p>{!! nl2br(e($para)) !!}</p>
                    @endif
                @endforeach
            </div>

            <div class="about-cta">
                <a class="btn" href="{{ route('portal.profile', 'sejarah') }}">Baca profil lengkap</a>
                <a class="about-link" href="{{ route('portal.profile', 'visi-misi') }}">Visi dan misi →</a>
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