{{-- Sambutan kepala sekolah: kiri = judul + foto/nama kepala sekolah, kanan = video (klik-untuk-putar) + bilah angka melayang.
     Tanpa link YouTube, bingkai memakai foto kepala sekolah.
     Variabel: $entity (sekolah), $ytId (ID video YouTube atau null), $stats (label => angka). --}}
@php
    $ytId = $ytId ?? null;
    $photo = $entity->headPhotoUrl();

    // Ikon dipilih dari kata pada label, jadi urutan statistik di controller tidak masalah
    $statIcon = function (string $label): string {
        $l = mb_strtolower($label);

        return match (true) {
            str_contains($l, 'siswa'), str_contains($l, 'murid') => 'users',
            str_contains($l, 'prestasi') => 'trophy',
            str_contains($l, 'tenaga'), str_contains($l, 'guru'), str_contains($l, 'pendidik'), str_contains($l, 'pegawai') => 'briefcase',
            default => 'star',
        };
    };
@endphp

<style>
    .sv {
        position: relative; isolation: isolate; overflow: hidden; color: rgba(255, 255, 255, .86);
        background:
            radial-gradient(900px 520px at 88% -8%, color-mix(in srgb, var(--accent-2, #f5b800) 16%, transparent), transparent 62%),
            radial-gradient(700px 480px at -6% 105%, rgba(255, 255, 255, .07), transparent 60%),
            linear-gradient(160deg, color-mix(in srgb, var(--primary) 42%, #06141f), #06141f 78%);
    }
    body .sv { padding-bottom: clamp(4.5rem, 9vw, 7.5rem); }

    /* Motif bintang geometris, samar dan memudar ke kanan bawah */
    .sv::before {
        content: ""; position: absolute; inset: 0; z-index: -1; pointer-events: none; opacity: .4;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='40' viewBox='0 0 40 40'%3E%3Cg fill='white' fill-opacity='.05' stroke='white' stroke-width='.9' stroke-opacity='.55'%3E%3Crect x='5.86' y='5.86' width='28.28' height='28.28'/%3E%3Crect x='5.86' y='5.86' width='28.28' height='28.28' transform='rotate(45 20 20)'/%3E%3C/g%3E%3Cg fill='none' stroke='white' stroke-width='.9' stroke-opacity='.5'%3E%3Ccircle cx='20' cy='20' r='4.5'/%3E%3Cpath d='M0 -4L4 0L0 4L-4 0ZM40 -4L44 0L40 4L36 0ZM0 36L4 40L0 44L-4 40ZM40 36L44 40L40 44L36 40Z'/%3E%3C/g%3E%3C/svg%3E");
        background-size: 56px 56px;
        -webkit-mask-image: linear-gradient(135deg, #000 0%, transparent 55%);
        mask-image: linear-gradient(135deg, #000 0%, transparent 55%);
    }

    .sv-grid { display: grid; grid-template-columns: minmax(0, 5fr) minmax(0, 7fr); gap: clamp(2rem, 5vw, 4.5rem); align-items: center; }

    /* Teks kiri */
    .sv-kicker { display: inline-flex; align-items: center; gap: .7rem; margin-bottom: 1rem; text-transform: uppercase; letter-spacing: .18em; font-size: .78rem; font-weight: 700; color: var(--accent-2, #f5b800); }
    .sv-kicker::before { content: ""; width: 34px; height: 2px; background: currentColor; }
    .sv h2 { color: #fff; font-size: clamp(2rem, 4vw, 3.1rem); line-height: 1.08; letter-spacing: -.03em; margin: 0; }
    .sv h2 em { font-style: normal; color: var(--accent-2, #f5b800); }
    .sv-sign { display: flex; align-items: center; gap: 1rem; margin-top: 2rem; }
    .sv-avatar { flex: none; width: 64px; height: 64px; padding: 3px; border-radius: 50%; object-fit: cover; background: linear-gradient(135deg, var(--accent-2, #f5b800), color-mix(in srgb, var(--accent-2, #f5b800) 30%, #fff)); }
    .sv-who strong { display: block; color: #fff; font-size: 1.15rem; line-height: 1.3; }
    .sv-who span { display: block; margin-top: .15rem; font-size: .76rem; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; color: var(--accent-2, #f5b800); }

    /* Video / foto */
    .sv-media { position: relative; padding-bottom: 2.8rem; }
    .sv-media::before {
        content: ""; position: absolute; z-index: 0; inset: -14px 18px calc(2.8rem + 14px) -14px; border-radius: 30px;
        border: 2px solid color-mix(in srgb, var(--accent-2, #f5b800) 70%, transparent);
    }
    .sv-frame { position: relative; z-index: 1; aspect-ratio: 16 / 9; overflow: hidden; border-radius: 24px; background: #000; border: 1px solid rgba(255, 255, 255, .2); box-shadow: 0 40px 80px -24px rgba(0, 0, 0, .65); }
    .sv-play { position: relative; display: block; width: 100%; height: 100%; }
    .sv-play img { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform .9s var(--ease-out); }
    .sv-play:hover img { transform: scale(1.04); }
    .sv-play::after { content: ""; position: absolute; inset: 0; background: linear-gradient(to top, rgba(6, 20, 36, .55), rgba(6, 20, 36, .08) 60%); }
    .sv-chip {
        position: absolute; z-index: 2; top: 1rem; left: 1rem; display: inline-flex; align-items: center; gap: .5rem; padding: .4rem 1rem;
        border-radius: 999px; font-size: .76rem; font-weight: 700; letter-spacing: .04em; color: #fff;
        background: rgba(6, 20, 36, .45); border: 1px solid rgba(255, 255, 255, .25); -webkit-backdrop-filter: blur(8px); backdrop-filter: blur(8px);
    }
    .sv-chip::before { content: ""; width: 8px; height: 8px; border-radius: 50%; background: var(--accent-2, #f5b800); }
    .sv-btn {
        position: absolute; z-index: 2; inset: 0; margin: auto; width: 88px; height: 88px; border-radius: 50%;
        display: grid; place-items: center; background: var(--accent-2, #f5b800); color: #3b2f00;
        box-shadow: 0 0 0 0 rgba(245, 184, 0, .55); animation: sv-pulse 2.4s infinite; transition: transform .3s var(--ease-out);
    }
    .sv-play:hover .sv-btn { transform: scale(1.08); }
    .sv-btn svg { width: 34px; height: 34px; margin-left: 4px; }
    .sv-frame iframe { display: block; width: 100%; height: 100%; border: 0; }
    @keyframes sv-pulse { 70% { box-shadow: 0 0 0 24px rgba(245, 184, 0, 0); } 100% { box-shadow: 0 0 0 0 rgba(245, 184, 0, 0); } }

    /* Tanpa video: foto kepala sekolah mengisi bingkai */
    .sv-photo { position: relative; width: 100%; height: 100%; display: block; }
    .sv-photo img { width: 100%; height: 100%; object-fit: cover; object-position: center 20%; display: block; }
    .sv-photo::after { content: ""; position: absolute; inset: 0; background: linear-gradient(to top, rgba(6, 20, 36, .55), rgba(6, 20, 36, 0) 55%); }
    .sv-initial {
        width: 100%; height: 100%; display: grid; place-items: center;
        font-size: clamp(5rem, 12vw, 9rem); font-weight: 800; color: rgba(255, 255, 255, .9);
        background: linear-gradient(135deg, color-mix(in srgb, var(--primary) 70%, #000), color-mix(in srgb, var(--primary) 30%, #06141f));
    }

    /* Bilah angka melayang menimpa tepi bawah video */
    .sv-stats {
        position: absolute; z-index: 3; left: clamp(1rem, 5%, 2.6rem); right: clamp(1rem, 5%, 2.6rem); bottom: 0;
        display: grid; grid-template-columns: repeat(3, 1fr); background: #fff; border-radius: 18px;
        border-top: 4px solid var(--accent-2, #f5b800); box-shadow: 0 30px 60px -20px rgba(0, 0, 0, .55);
    }
    .sv-stat { display: flex; align-items: center; gap: .9rem; min-width: 0; padding: 1.15rem 1.3rem; }
    .sv-stat + .sv-stat { border-left: 1px solid var(--line); }
    .sv-ic { flex: none; width: 46px; height: 46px; border-radius: 14px; display: grid; place-items: center; color: var(--ink); background: color-mix(in srgb, var(--accent-2, #f5b800) 24%, #fff); }
    .sv-ic .ico { width: 1.4rem; height: 1.4rem; }
    .sv-num { font-weight: 800; font-size: clamp(1.7rem, 2.8vw, 2.3rem); line-height: 1.05; letter-spacing: -.03em; font-variant-numeric: tabular-nums; color: var(--ink); }
    .sv-label { margin-top: .25rem; font-size: .72rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; line-height: 1.35; color: var(--muted); }

    @media (prefers-reduced-motion: reduce) { .sv-btn { animation: none; } }

    @media (max-width: 960px) {
        .sv-grid { grid-template-columns: 1fr; gap: 2.6rem; }
        .sv-media { padding-bottom: 0; }
        .sv-media::before { inset: -8px 10px 8px -8px; border-radius: 26px; }
        .sv-btn { width: 76px; height: 76px; }
        .sv-btn svg { width: 30px; height: 30px; }
        .sv-stats { position: relative; left: auto; right: auto; bottom: auto; margin: -1.8rem 1rem 0; }
        .sv-stat { flex-direction: column; text-align: center; gap: .5rem; padding: 1rem .4rem; }
        .sv-ic { width: 38px; height: 38px; border-radius: 12px; }
        .sv-ic .ico { width: 1.15rem; height: 1.15rem; }
        .sv-num { font-size: 1.5rem; }
        .sv-label { font-size: .6rem; letter-spacing: .05em; }
    }
</style>

<section class="section sv">
    <div class="wrap sv-grid">

        <div class="sv-text" data-reveal>
            <span class="sv-kicker">Sambutan</span>
            <h2>Sambutan <em>{{ $entity->head_title ?: 'Kepala Sekolah' }}</em></h2>

            @if ($entity->head_name || $photo)
                <div class="sv-sign">
                    @if ($photo)
                        <img class="sv-avatar" src="{{ $photo }}" alt="" loading="lazy">
                    @endif
                    <div class="sv-who">
                        @if ($entity->head_name)<strong>{{ $entity->head_name }}</strong>@endif
                        <span>{{ $entity->head_title ?: 'Kepala Sekolah' }}</span>
                    </div>
                </div>
            @endif
        </div>

        <div class="sv-media" data-reveal>
            <div class="sv-frame">
                @if ($ytId)
                    {{-- Tanpa JavaScript, tautan ini tetap membuka video di YouTube --}}
                    <a class="sv-play" href="https://www.youtube.com/watch?v={{ $ytId }}" data-sv-yt="{{ $ytId }}"
                       target="_blank" rel="noopener"
                       aria-label="Putar video sambutan {{ $entity->head_title }} {{ $entity->head_name }}">
                        <img src="https://i.ytimg.com/vi/{{ $ytId }}/maxresdefault.jpg" alt="" loading="lazy"
                             onload="if (this.naturalWidth < 200) this.src = this.src.replace('maxresdefault', 'hqdefault')"
                             onerror="this.onerror = null; this.src = this.src.replace('maxresdefault', 'hqdefault')">
                        <span class="sv-chip">Video Sambutan</span>
                        <span class="sv-btn" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5.14v13.72a1 1 0 0 0 1.52.85l11-6.86a1 1 0 0 0 0-1.7l-11-6.86A1 1 0 0 0 8 5.14Z"/></svg>
                        </span>
                    </a>
                @else
                    <div class="sv-photo">
                        @if ($photo)
                            <img src="{{ $photo }}" alt="{{ $entity->head_title }} {{ $entity->head_name }}" loading="lazy">
                        @else
                            <div class="sv-initial" aria-hidden="true">{{ mb_substr((string) ($entity->head_name ?: $entity->name), 0, 1) }}</div>
                        @endif
                        <span class="sv-chip">{{ $entity->head_title ?: 'Kepala Sekolah' }}</span>
                    </div>
                @endif
            </div>

            <div class="sv-stats">
                @foreach ($stats as $label => $value)
                    <div class="sv-stat">
                        <span class="sv-ic">@include('partials.icon', ['name' => $statIcon($label)])</span>
                        <div>
                            <div class="sv-num" data-count="{{ (int) $value }}">{{ number_format((int) $value, 0, ',', '.') }}</div>
                            <div class="sv-label">{{ $label }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>
</section>

<script>
    (function () {
        document.querySelectorAll('[data-sv-yt]').forEach(function (a) {
            a.addEventListener('click', function (e) {
                e.preventDefault();
                var f = document.createElement('iframe');
                f.src = 'https://www.youtube-nocookie.com/embed/' + a.getAttribute('data-sv-yt') + '?autoplay=1&rel=0&modestbranding=1';
                f.title = a.getAttribute('aria-label') || 'Video';
                f.allow = 'accelerometer; autoplay; encrypted-media; picture-in-picture; fullscreen';
                f.allowFullscreen = true;
                a.parentNode.replaceChild(f, a);
            });
        });
    })();
</script>