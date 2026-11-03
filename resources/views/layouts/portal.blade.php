@php
    $profilePages = [
        'sejarah' => 'Sejarah',
        'visi-misi' => 'Visi, Misi, dan Tujuan',
        'struktur' => 'Struktur Organisasi',
        'pegawai' => 'Pegawai',
    ];
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
    @vite('resources/css/site.css')
    <style>:root { --primary: {{ $foundation->safeColor() }}; }</style>
</head>
<body>

<header class="site-header">
    <div class="wrap bar">
        <a class="brand" href="{{ route('portal') }}">
            @if ($foundation->logoUrl())
                <img src="{{ $foundation->logoUrl() }}" alt="Logo {{ $foundation->name }}">
            @endif
            <span>{{ $foundation->name }}</span>
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

<main>
    @yield('content')
</main>

<footer class="site-footer">
    <div class="wrap footer-grid">
        <div>
            <div class="brand brand-footer">
                @if ($foundation->logoUrl())
                    <img src="{{ $foundation->logoUrl() }}" alt="Logo {{ $foundation->name }}">
                @endif
                <span>{{ $foundation->name }}</span>
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
                @foreach (['facebook' => 'Facebook', 'instagram' => 'Instagram', 'youtube' => 'YouTube', 'tiktok' => 'TikTok'] as $key => $label)
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
        if (!btn || !nav) return;
        btn.addEventListener('click', function () {
            var open = nav.classList.toggle('open');
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    })();
</script>
</body>
</html>