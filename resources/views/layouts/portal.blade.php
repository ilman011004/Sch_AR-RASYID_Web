@php
    $profilePages = [
        'sejarah' => 'Sejarah',
        'visi-misi' => 'Visi, Misi, dan Tujuan',
        'struktur' => 'Struktur Organisasi',
        'pegawai' => 'Pegawai',
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
    <title>@yield('title', $foundation->name)</title>
    <meta name="description" content="@yield('description', \Illuminate\Support\Str::limit((string) $foundation->description, 150))">
    @if ($foundation->logoUrl())
        <link rel="icon" href="{{ $foundation->logoUrl() }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite('resources/css/site.css')
    <style>:root { --primary: {{ $foundation->safeColor() }}; }</style>

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

        /* Banner judul halaman dalam (Profil, Berita, Pengumuman, dll.) */
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

            .hero-page .site-top:not(.scrolled) .nav > a,
            .hero-page .site-top:not(.scrolled) .nav > .dropdown > button { color: #fff; }

            .hero-page .site-top:not(.scrolled) .nav > a:hover,
            .hero-page .site-top:not(.scrolled) .nav > .dropdown > button:hover {
                background: rgba(255, 255, 255, .16); color: #fff;
            }
            .hero-page .site-top:not(.scrolled) .nav > a.active,
            .hero-page .site-top:not(.scrolled) .nav > .dropdown > button.active {
                background: rgba(255, 255, 255, .22); color: #fff;
            }
        }
    </style>

    {{-- Aksen hijau permanen (tidak ikut warna utama dari pengaturan admin). Ubah --accent untuk mengganti hijaunya. --}}
    <style>
        :root { --accent: #22a06b; --accent-2: #f5b800; }

        /* Tombol utama di hero */
        .hero .btn:not(.btn-outline) { background: var(--accent); border-color: var(--accent); color: #fff; }

        /* Menu aktif: garis hijau di bawah */
        .nav > a.active, .nav > .dropdown > button.active { box-shadow: inset 0 -3px 0 var(--accent); }
        .site-top.scrolled .nav > a:hover,
        .site-top.scrolled .nav > .dropdown > button:hover { color: var(--accent); background: color-mix(in srgb, var(--accent) 10%, #fff); }
        .site-top.scrolled .nav > a.active,
        .site-top.scrolled .nav > .dropdown > button.active { color: var(--accent); background: color-mix(in srgb, var(--accent) 10%, #fff); }
        .dropdown-menu a:hover { color: var(--accent); background: color-mix(in srgb, var(--accent) 10%, #fff); }

        /* Judul bagian, label, tautan */
        .kicker { color: var(--accent); }
        .sec-head h2::after, .section-head h2::after { background: var(--accent); }
        .more, .section-head a { color: var(--accent); }
        .notice-list a:hover { color: var(--accent); }

        /* Kartu dan kotak */
        /* Kotak akses cepat: aksen kuning (ubah --accent-2 untuk mengganti kuningnya) */
        .quick a { border-top: 4px solid var(--accent-2); }
        .quick .qi { background: color-mix(in srgb, var(--accent-2) 22%, #fff); }
        .quick a:hover { border-color: var(--accent-2); }
        .panel-box { border-top: 4px solid var(--accent); }
        .portal-card { border-top-color: var(--accent); }
        .welcome-photo::before { background: color-mix(in srgb, var(--accent) 22%, #fff); }
        .welcome-sign { border-left-color: var(--accent); }
        ul.check li::before { background: var(--accent); }
        ol.steps li::before { color: var(--accent); background: color-mix(in srgb, var(--accent) 14%, #fff); }

        /* Footer */
        .site-footer { border-top: 6px solid var(--accent); }
        .site-footer h4 { color: var(--accent); }
    </style>
</head>
<body class="{{ $bodyClass }}">

<div class="site-top" data-site-top>

<div class="topbar">
    <div class="wrap">
        <div class="group">
            @if ($foundation->phone)<span>📞 {{ $foundation->phone }}</span>@endif
            @if ($foundation->email)<a href="mailto:{{ $foundation->email }}">✉ {{ $foundation->email }}</a>@endif
            @if ($foundation->whatsapp)<a href="https://wa.me/{{ $foundation->whatsapp }}" target="_blank" rel="noopener">WhatsApp</a>@endif
        </div>
        <div class="group">
            @foreach ($socials as $key => $label)
                @if ($foundation->{$key})
                    <a href="{{ $foundation->{$key} }}" target="_blank" rel="noopener">{{ $label }}</a>
                @endif
            @endforeach
        </div>
    </div>
</div>

<header class="site-header">
    <div class="wrap bar">
        <a class="brand" href="{{ route('portal') }}">
            @if ($foundation->logoUrl())
                <img src="{{ $foundation->logoUrl() }}" alt="Logo {{ $foundation->name }}">
            @endif
            <span class="brand-text">
                <b>{{ $foundation->name }}</b>
                <small>Yayasan Pendidikan</small>
            </span>
        </a>

        <button class="nav-toggle" type="button" aria-label="Menu" aria-expanded="false" data-nav-toggle>☰</button>

        <nav class="nav" data-nav>
            <a href="{{ route('portal') }}" @class(['active' => request()->routeIs('portal')])>Beranda</a>

            <div class="dropdown">
                <button type="button" @class(['active' => request()->routeIs('portal.profile')])>Profil</button>
                <div class="dropdown-menu">
                    @foreach ($profilePages as $key => $label)
                        <a href="{{ route('portal.profile', $key) }}">{{ $label }}</a>
                    @endforeach
                </div>
            </div>

            <div class="dropdown">
                <button type="button">Jenjang</button>
                <div class="dropdown-menu">
                    @foreach ($jenjang as $s)
                        @if ($s->is_active)
                            <a href="{{ route('home', $s) }}">{{ $s->name }}</a>
                        @else
                            <span class="soon">{{ $s->name }} · segera hadir</span>
                        @endif
                    @endforeach
                </div>
            </div>

            <div class="dropdown">
                <button type="button" @class(['active' => request()->routeIs('portal.spmb', 'portal.announcements')])>Info SPMB</button>
                <div class="dropdown-menu">
                    <a href="{{ route('portal.spmb') }}">Pendaftaran</a>
                    <a href="{{ route('portal.announcements') }}">Pengumuman</a>
                </div>
            </div>

            <a href="{{ route('portal.news') }}" @class(['active' => request()->routeIs('portal.news*')])>Berita dan Informasi</a>
        </nav>
    </div>
</header>

</div>

<main>
    {!! $pageContent !!}
</main>

<footer class="site-footer">
    <div class="wrap footer-grid">
        <div>
            <div class="brand brand-footer">
                @if ($foundation->logoUrl())
                    <img src="{{ $foundation->logoUrl() }}" alt="Logo {{ $foundation->name }}">
                @endif
                <span class="brand-text">
                    <b>{{ $foundation->name }}</b>
                    <small>Yayasan Pendidikan</small>
                </span>
            </div>
            @if ($foundation->tagline)
                <p class="muted">{{ $foundation->tagline }}</p>
            @endif
        </div>

        <div>
            <h4>Kontak</h4>
            <ul class="plain">
                @if ($foundation->address)<li>{{ $foundation->address }}</li>@endif
                @if ($foundation->phone)<li>Telp. {{ $foundation->phone }}</li>@endif
                @if ($foundation->email)<li>{{ $foundation->email }}</li>@endif
                @if ($foundation->whatsapp)
                    <li><a href="https://wa.me/{{ $foundation->whatsapp }}" rel="noopener" target="_blank">WhatsApp {{ $foundation->whatsapp }}</a></li>
                @endif
            </ul>
        </div>

        <div>
            <h4>Jenjang</h4>
            <ul class="plain">
                @foreach ($jenjang->where('is_active', true) as $s)
                    <li><a href="{{ route('home', $s) }}">{{ $s->name }}</a></li>
                @endforeach
            </ul>
        </div>

        <div>
            <h4>Media Sosial</h4>
            <ul class="plain">
                @foreach ($socials as $key => $label)
                    @if ($foundation->{$key})
                        <li><a href="{{ $foundation->{$key} }}" rel="noopener" target="_blank">{{ $label }}</a></li>
                    @endif
                @endforeach
            </ul>
        </div>
    </div>

    <div class="copyright">
        <div class="wrap">© {{ now()->year }} {{ $foundation->name }}</div>
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