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
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', $school->name)</title>
    <meta name="description" content="@yield('description', \Illuminate\Support\Str::limit((string) $school->description, 150))">
    @if ($school->logoUrl())
        <link rel="icon" href="{{ $school->logoUrl() }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite('resources/css/site.css')
    <style>:root { --primary: {{ $school->safeColor() }}; }</style>
</head>
<body>

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
                    <form method="post" action="{{ route('ppdb.logout', $school) }}" class="nav-user">
                        @csrf
                        <span>{{ \Illuminate\Support\Str::limit(auth()->user()->name, 18) }}</span>
                        <button type="submit">Keluar</button>
                    </form>
                @endif
            @else
                <a class="btn btn-sm btn-outline" href="{{ route('ppdb.login', $school) }}">Masuk</a>
            @endauth
        </nav>
    </div>
</header>

<main>
    @yield('content')
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
                @if ($school->phone)<li>Telp. {{ $school->phone }}</li>@endif
                @if ($school->email)<li>{{ $school->email }}</li>@endif
                @if ($school->whatsapp)
                    <li><a href="https://wa.me/{{ $school->whatsapp }}" rel="noopener" target="_blank">WhatsApp {{ $school->whatsapp }}</a></li>
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
        if (!btn || !nav) return;
        btn.addEventListener('click', function () {
            var open = nav.classList.toggle('open');
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    })();
</script>
</body>
</html>