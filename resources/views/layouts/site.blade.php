@php
    $profilePages = [
        'sejarah' => 'Sejarah',
        'visi-misi' => 'Visi, Misi, dan Tujuan',
        'struktur' => 'Struktur Organisasi',
        'pegawai' => 'Pegawai',
    ];
    $programPages = [
        'unggulan' => 'Program Unggulan',
        'kurikulum' => 'Kurikulum',
        'ekstrakurikuler' => 'Ekstrakurikuler',
    ];
    $socials = ['facebook' => 'Facebook', 'instagram' => 'Instagram', 'youtube' => 'YouTube', 'tiktok' => 'TikTok'];

    // Render isi halaman lebih dulu supaya bisa dicek: halaman yang punya banner judul (.page-title)
    // otomatis memakai navbar transparan yang sama seperti beranda. Tidak perlu mengubah tiap halaman.
    $pageContent = $__env->yieldContent('content');
    $bodyClass = trim($__env->yieldContent('body_class'));
    if (! str_contains($bodyClass, 'hero-page') && str_contains($pageContent, 'class="page-title"')) {
        $bodyClass = trim($bodyClass.' hero-page');
    }
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{-- BARU: kirim asal situs ke YouTube agar embed video tidak ditolak (Error 153) --}}
    <meta name="referrer" content="strict-origin-when-cross-origin">
    <title>@yield('title', $school->name)</title>
    <meta name="description" content="@yield('description', \Illuminate\Support\Str::limit((string) $school->description, 150))">
    <link rel="canonical" href="{{ url()->current() }}">
    @if ($school->logoUrl())
        <link rel="icon" href="{{ $school->logoUrl() }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite('resources/css/site.css')
    <style>:root { --primary: {{ $school->safeColor() }}; }</style>

    {{-- Structured Data (Schema.org / JSON-LD) untuk Google Organization --}}
    @php
        $sameAsSchool = [];
        foreach ($socials as $k => $l) {
            if (filled($school->{$k})) {
                $sameAsSchool[] = $school->{$k};
            }
        }
        $jsonLdSchool = [
            '@context' => 'https://schema.org',
            '@type' => 'School',
            'name' => $school->name,
            'url' => route('home', $school),
            'logo' => $school->logoUrl() ?: null,
            'description' => $school->description ?: $school->tagline,
            'telephone' => $school->phone ?: null,
            'email' => $school->email ?: null,
            'address' => $school->address ? [
                '@type' => 'PostalAddress',
                'streetAddress' => $school->address,
                'addressCountry' => 'ID',
            ] : null,
            'sameAs' => !empty($sameAsSchool) ? array_values($sameAsSchool) : null,
        ];
    @endphp
    <script type="application/ld+json">
        {!! json_encode(array_filter($jsonLdSchool), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>

    {{-- Navbar transparan di atas hero / banner judul (kelas hero-page dipasang otomatis, lihat bagian atas file) --}}
    <style>
        /* Halaman biasa: pembungkus tidak mengubah tata letak, header tetap sticky seperti semula */
        .site-top { display: contents; }

        /* Halaman dengan hero atau banner judul: topbar + navbar melayang di atas gambar */
        .hero-page .site-top { display: block; position: fixed; top: 0; left: 0; right: 0; z-index: 50; }

        .hero-page .topbar {
            background: transparent; color: #fff;
            border-bottom: 1px solid rgba(255, 255, 255, .22);
            max-height: 80px; overflow: hidden;
            transition: max-height .3s ease, opacity .25s ease, border-color .25s ease;
        }
        .hero-page .topbar a { color: #fff; }

        .hero-page .site-header {
            position: relative;
            background: transparent;
            border-bottom-color: transparent;
            box-shadow: none;
            -webkit-backdrop-filter: none; backdrop-filter: none;
            transition: background .3s ease, box-shadow .3s ease, border-color .3s ease;
        }

        /* Setelah di-scroll: navbar putih agak transparan, topbar disembunyikan */
        .hero-page .site-top.scrolled .topbar { max-height: 0; opacity: 0; border-bottom-width: 0; }
        .hero-page .site-top.scrolled .site-header {
            background: rgba(255, 255, 255, .86);
            -webkit-backdrop-filter: blur(12px); backdrop-filter: blur(12px);
            border-bottom-color: var(--line);
            box-shadow: 0 6px 24px rgba(16, 24, 40, .08);
        }

        /* Saat transparan: logo teks dan tombol menu berwarna putih */
        .hero-page .site-top:not(.scrolled) .brand-text b,
        .hero-page .site-top:not(.scrolled) .brand-text small { color: #fff; }
        .hero-page .site-top:not(.scrolled) .brand-text small { opacity: .85; }
        .hero-page .site-top:not(.scrolled) .nav-toggle { color: #fff; border-color: rgba(255, 255, 255, .55); }

        /* Hero dimulai dari tepi atas layar, beri ruang agar teks tidak tertutup navbar */
        .hero-page .hero { padding-top: 7rem; }

        /* Banner judul halaman dalam (Profil, Program, Berita, dll.) */
        .hero-page .page-title { position: relative; padding-top: 8rem; }
        .hero-page .page-title::before {
            content: ""; position: absolute; inset: 0; pointer-events: none;
            background: linear-gradient(180deg, rgba(10, 30, 45, .38), rgba(10, 30, 45, 0) 65%);
        }
        .hero-page .page-title > * { position: relative; z-index: 1; }

        /* Motif bintang geometris Islami di banner judul (SVG ditanam langsung, tidak perlu file gambar) */
        .page-title { position: relative; overflow: hidden; border-bottom: 4px solid var(--accent-2, #f5b800); }
        .page-title::after {
            content: ""; position: absolute; inset: 0; z-index: 0; pointer-events: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='40' viewBox='0 0 40 40'%3E%3Cg fill='white' fill-opacity='.05' stroke='white' stroke-width='.9' stroke-opacity='.55'%3E%3Crect x='5.86' y='5.86' width='28.28' height='28.28'/%3E%3Crect x='5.86' y='5.86' width='28.28' height='28.28' transform='rotate(45 20 20)'/%3E%3C/g%3E%3Cg fill='none' stroke='white' stroke-width='.9' stroke-opacity='.5'%3E%3Ccircle cx='20' cy='20' r='4.5'/%3E%3Cpath d='M0 -4L4 0L0 4L-4 0ZM40 -4L44 0L40 4L36 0ZM0 36L4 40L0 44L-4 40ZM40 36L44 40L40 44L36 40Z'/%3E%3C/g%3E%3C/svg%3E");
            background-size: 56px 56px;
            opacity: .7;
            /* motif samar di kiri (di belakang judul) dan makin jelas ke kanan */
            -webkit-mask-image: linear-gradient(90deg, rgba(0, 0, 0, .22) 0%, #000 75%);
            mask-image: linear-gradient(90deg, rgba(0, 0, 0, .22) 0%, #000 75%);
        }
        .page-title > * { position: relative; z-index: 1; }

        @media (min-width: 961px) {
            .hero-page .hero { padding-top: 9.5rem; }
            .hero-page .page-title { padding-top: 9.5rem; }

            .hero-page .site-top:not(.scrolled) .nav > a:not(.btn),
            .hero-page .site-top:not(.scrolled) .nav > .dropdown > button { color: #fff; }

            .hero-page .site-top:not(.scrolled) .nav > a:not(.btn):hover,
            .hero-page .site-top:not(.scrolled) .nav > .dropdown > button:hover {
                background: rgba(255, 255, 255, .16); color: #fff;
            }
            .hero-page .site-top:not(.scrolled) .nav > a.active,
            .hero-page .site-top:not(.scrolled) .nav > .dropdown > button.active {
                background: rgba(255, 255, 255, .22); color: #fff;
            }
        }
    </style>

    {{-- Warna aksen. Hijau (--accent) hanya untuk tombol ajakan seperti "Pendaftaran".
         Elemen lain memakai biru tua (--ink) dan kuning (--accent-2). Ubah nilai di bawah untuk mengganti warna. --}}
    <style>
        :root { --accent: #2d8f6b; --accent-2: #f5b800; --ink: #24485c; }

        /* Tombol utama di hero (satu-satunya blok hijau besar) */
        .hero .btn:not(.btn-outline) {
            background: var(--accent, #2d8f6b);
            border-color: var(--accent, #2d8f6b);
            color: #fff !important;
            font-weight: 700;
            box-shadow: 0 8px 20px rgba(0, 0, 0, .2);
            transition: all .25s ease;
        }
        .hero .btn:not(.btn-outline):hover {
            background: color-mix(in srgb, var(--accent, #2d8f6b) 85%, #000);
            border-color: color-mix(in srgb, var(--accent, #2d8f6b) 85%, #000);
            color: #fff !important;
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(0, 0, 0, .28);
        }

        /* Tombol kedua di hero (Lihat Profil): sebelum diarahkan transparan border putih, pas diarahkan kursor jadi biru tulisan putih */
        .hero .btn.btn-outline {
            background: transparent !important;
            border: 2px solid rgba(255, 255, 255, .85) !important;
            color: #fff !important;
            font-weight: 700 !important;
            transition: all .25s ease !important;
        }
        .hero .btn.btn-outline:hover {
            background: var(--primary, #0f766e) !important;
            border-color: var(--primary, #0f766e) !important;
            color: #fff !important;
            transform: translateY(-2px);
            box-shadow: 0 10px 24px rgba(0, 0, 0, .25) !important;
        }

        /* Menu aktif: garis kuning di bawah */
        .nav > a.active, .nav > .dropdown > button.active { box-shadow: inset 0 -3px 0 var(--accent-2); }
        .site-top.scrolled .nav > a:not(.btn):hover,
        .site-top.scrolled .nav > .dropdown > button:hover { color: var(--ink); background: color-mix(in srgb, var(--ink) 8%, #fff); }
        .site-top.scrolled .nav > a.active,
        .site-top.scrolled .nav > .dropdown > button.active { color: var(--ink); background: color-mix(in srgb, var(--ink) 8%, #fff); }
        .dropdown-menu a:hover { color: var(--ink); background: color-mix(in srgb, var(--ink) 8%, #fff); }

        /* Judul bagian, label, tautan */
        .kicker { color: var(--ink); }
        .sec-head h2::after, .section-head h2::after { background: var(--accent-2); }
        .more, .section-head a { color: var(--ink); }
        .notice-list a:hover { color: var(--ink); }

        /* Kartu dan kotak */
        /* Kotak akses cepat: aksen kuning (ubah --accent-2 untuk mengganti kuningnya) */
        .quick a { border-top: 4px solid var(--accent-2); }
        .quick .qi { background: color-mix(in srgb, var(--accent-2) 22%, #fff); }
        .quick a:hover { border-color: var(--accent-2); }
        .panel-box { border-top: 4px solid var(--ink); }
        .portal-card { border-top-color: var(--ink); }
        .welcome-photo::before { background: color-mix(in srgb, var(--accent-2) 28%, #fff); }
        .welcome-sign { border-left-color: var(--accent-2); }
        ul.check li::before { background: var(--ink); }
        ol.steps li::before { color: var(--ink); background: color-mix(in srgb, var(--ink) 12%, #fff); }

        /* Footer */
        .site-footer { border-top: 6px solid var(--accent-2); }
        .site-footer h4 { color: var(--accent-2); }
    </style>

    {{-- Tambahan khusus situs sekolah: tombol, pita pendaftaran, statistik, ikon --}}
    <style>
        .nav a.btn:not(.btn-outline) { background: var(--accent); border-color: var(--accent); color: #fff; }
        .cta-band { border-top: 6px solid var(--accent-2); }
        .cta-band .btn { background: var(--accent-2); border-color: var(--accent-2); color: #3b2f00; }
        .stat .num { color: var(--accent-2); }
        .icon { background: color-mix(in srgb, var(--accent-2) 22%, #fff); }

        /* Ikon Navigasi Profil Akun */
        .nav-user-dropdown { position: relative; padding: .6rem 0; }
        .nav-user-btn {
            display: inline-flex; align-items: center; justify-content: center;
            width: 40px; height: 40px; border-radius: 50%;
            background: color-mix(in srgb, var(--primary) 12%, #fff);
            border: 2px solid var(--primary); color: var(--primary);
            cursor: pointer; padding: 0; transition: all .2s ease;
        }
        .nav-user-btn::after { display: none !important; }
        .nav-user-btn:hover {
            background: var(--primary); color: #fff; transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,.15);
        }
        .nav-user-avatar { display: inline-flex; align-items: center; justify-content: center; }
        .nav-user-avatar svg { width: 20px; height: 20px; }

        @media (min-width: 961px) {
            .hero-page .site-top:not(.scrolled) .nav > a.btn-outline { color: #fff; border-color: rgba(255, 255, 255, .75); }
            .hero-page .site-top:not(.scrolled) .nav > a.btn-outline:hover { background: #fff; color: var(--primary); }
            .hero-page .site-top:not(.scrolled) .nav-user-dropdown > .nav-user-btn { color: #fff; border-color: rgba(255, 255, 255, .85); background: rgba(255, 255, 255, .2); }
            .hero-page .site-top:not(.scrolled) .nav-user-dropdown > .nav-user-btn:hover { background: #fff; color: var(--primary); }
        }
    </style>
</head>
<body class="{{ $bodyClass }}">

<div class="site-top" data-site-top>

<div class="topbar">
    <div class="wrap">
        <div class="group">
            @if ($school->phone)<span>📞 {{ $school->phone }}</span>@endif
            @if ($school->email)<a href="mailto:{{ $school->email }}">✉ {{ $school->email }}</a>@endif
            @if ($school->whatsapp)<a href="https://wa.me/{{ $school->whatsapp }}" target="_blank" rel="noopener">WhatsApp</a>@endif
        </div>
        <div class="group">
            @foreach ($socials as $key => $label)
                @if ($school->{$key})
                    <a href="{{ $school->{$key} }}" target="_blank" rel="noopener">{{ $label }}</a>
                @endif
            @endforeach
            <a href="{{ route('portal') }}">Situs {{ $school->foundation_name ?: 'Yayasan' }}</a>
        </div>
    </div>
</div>

<header class="site-header">
    <div class="wrap bar">
        <a class="brand" href="{{ route('home', $school) }}">
            @if ($school->logoUrl())
                <img src="{{ $school->logoUrl() }}" alt="Logo {{ $school->name }}">
            @endif
            <span class="brand-text">
                <b>{{ $school->name }}</b>
                @if ($school->foundation_name)<small>{{ $school->foundation_name }}</small>@endif
            </span>
        </a>

        <button class="nav-toggle" type="button" aria-label="Menu" aria-expanded="false" data-nav-toggle>☰</button>

        <nav class="nav" data-nav>
            <a href="{{ route('home', $school) }}" @class(['active' => request()->routeIs('home')])>Beranda</a>

            <div class="dropdown">
                <button type="button" @class(['active' => request()->routeIs('profile')])>Profil</button>
                <div class="dropdown-menu">
                    @foreach ($profilePages as $key => $label)
                        <a href="{{ route('profile', ['school' => $school, 'page' => $key]) }}">{{ $label }}</a>
                    @endforeach
                </div>
            </div>

            <div class="dropdown">
                <button type="button" @class(['active' => request()->routeIs('programs')])>Program Akademik</button>
                <div class="dropdown-menu">
                    @foreach ($programPages as $key => $label)
                        <a href="{{ route('programs', ['school' => $school, 'page' => $key]) }}">{{ $label }}</a>
                    @endforeach
                </div>
            </div>

            <a href="{{ route('news', $school) }}" @class(['active' => request()->routeIs('news*')])>Berita</a>
            <a href="{{ route('gallery', $school) }}" @class(['active' => request()->routeIs('gallery')])>Galeri</a>
            <a href="{{ route('contact', $school) }}" @class(['active' => request()->routeIs('contact')])>Kontak</a>
            <a class="btn btn-sm" href="{{ route('ppdb', $school) }}">Pendaftaran</a>

            @auth
                @if (auth()->user()->isParent())
                    <div class="dropdown nav-user-dropdown" style="margin-left: .5rem;">
                        <button type="button" class="nav-user-btn" title="Akun: {{ auth()->user()->name }}" aria-label="Menu Akun Saya">
                            <span class="nav-user-avatar">
                                @include('partials.icon', ['name' => 'user'])
                            </span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end" style="right: 0; left: auto; min-width: 220px;">
                            <div style="padding: .6rem .8rem; border-bottom: 1px solid var(--line); margin-bottom: .4rem;">
                                <div style="font-weight: 700; color: var(--ink); font-size: .95rem; line-height: 1.2;">{{ auth()->user()->name }}</div>
                                <div style="font-size: .8rem; color: var(--muted); margin-top: .2rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ auth()->user()->email }}</div>
                            </div>
                            <a href="{{ route('ppdb.form', $school) }}" style="display: flex; align-items: center; gap: .5rem;">
                                <span>📝</span> Formulir PPDB
                            </a>
                            <a href="{{ route('ppdb.status', $school) }}" style="display: flex; align-items: center; gap: .5rem;">
                                <span>🔍</span> Cek Status
                            </a>
                            <form method="post" action="{{ route('ppdb.logout', $school) }}" style="margin: .3rem 0 0; padding-top: .3rem; border-top: 1px solid var(--line);">
                                @csrf
                                <button type="submit" style="width: 100%; text-align: left; background: none; border: none; padding: .55rem .8rem; color: #dc2626; font-weight: 600; font-size: .9rem; cursor: pointer; border-radius: 8px; display: flex; align-items: center; gap: .5rem;">
                                    <span>🚪</span> Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            @else
                <a class="btn btn-sm btn-outline" href="{{ route('ppdb.login', $school) }}">Masuk</a>
            @endauth
        </nav>
    </div>
</header>

</div>

<main>
    {!! $pageContent !!}
</main>

@if ($school->ppdbIsOpen() && ! request()->routeIs('ppdb*'))
    <section class="cta-band">
        <div class="wrap">
            <div>
                <h3>Pendaftaran siswa baru {{ $school->ppdb_year ?: '' }} telah dibuka</h3>
                <p>Daftarkan putra-putri Anda di {{ $school->name }} secara online.</p>
            </div>
            <a class="btn" href="{{ route('ppdb', $school) }}">Daftar Sekarang</a>
        </div>
    </section>
@endif

<footer class="site-footer">
    <div class="wrap footer-grid">
        <div>
            <div class="brand brand-footer">
                @if ($school->logoUrl())
                    <img src="{{ $school->logoUrl() }}" alt="Logo {{ $school->name }}">
                @endif
                <span class="brand-text">
                    <b>{{ $school->name }}</b>
                    @if ($school->foundation_name)<small>{{ $school->foundation_name }}</small>@endif
                </span>
            </div>
            @if ($school->tagline)
                <p class="muted">{{ $school->tagline }}</p>
            @endif
        </div>

        <div>
            <h4>Kontak</h4>
            <ul class="plain">
                @if ($school->address)<li>{{ $school->address }}</li>@endif
                @if ($school->phone)
                    @php
                        $cleanPhone = preg_replace('/[^0-9+]/', '', $school->phone);
                    @endphp
                    <li>Telp. <a href="tel:{{ $cleanPhone }}">{{ $school->phone }}</a></li>
                @endif
                @if ($school->email)
                    <li><a href="mailto:{{ $school->email }}">{{ $school->email }}</a></li>
                @endif
                @if ($school->whatsapp)
                    @php
                        $cleanWa = preg_replace('/[^0-9]/', '', $school->whatsapp);
                    @endphp
                    <li><a href="https://wa.me/{{ $cleanWa }}" rel="noopener" target="_blank">WhatsApp {{ $school->whatsapp }}</a></li>
                @endif
            </ul>
        </div>

        <div>
            <h4>Navigasi</h4>
            <ul class="plain">
                <li><a href="{{ route('profile', ['school' => $school, 'page' => 'sejarah']) }}">Profil</a></li>
                <li><a href="{{ route('programs', ['school' => $school, 'page' => 'unggulan']) }}">Program Akademik</a></li>
                <li><a href="{{ route('news', $school) }}">Berita</a></li>
                <li><a href="{{ route('gallery', $school) }}">Galeri</a></li>
                <li><a href="{{ route('ppdb', $school) }}">Pendaftaran Siswa</a></li>
                <li><a href="{{ route('contact', $school) }}">Kontak</a></li>
                <li><a href="{{ route('portal') }}">Situs {{ $school->foundation_name ?: 'Yayasan' }}</a></li>
            </ul>
        </div>

        <div>
            <h4>Media Sosial</h4>
            <ul class="plain">
                @foreach ($socials as $key => $label)
                    @if ($school->{$key})
                        <li><a href="{{ $school->{$key} }}" rel="noopener" target="_blank">{{ $label }}</a></li>
                    @endif
                @endforeach
            </ul>
        </div>
    </div>

    <div class="copyright">
        <div class="wrap">
            © {{ now()->year }} {{ $school->name }}
            @if ($school->foundation_name) · {{ $school->foundation_name }} @endif
        </div>
    </div>
</footer>

<script>
    (function () {
        var btn = document.querySelector('[data-nav-toggle]');
        var nav = document.querySelector('[data-nav]');
        if (btn && nav) {
            btn.addEventListener('click', function () {
                var open = nav.classList.toggle('open');
                btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            });
        }

        // Navbar transparan -> putih saat halaman di-scroll (halaman hero dan banner judul)
        var top = document.querySelector('[data-site-top]');
        if (top && document.body.classList.contains('hero-page')) {
            var onScroll = function () { top.classList.toggle('scrolled', window.scrollY > 40); };
            onScroll();
            window.addEventListener('scroll', onScroll, { passive: true });
        }
    })();
</script>
</body>
</html>