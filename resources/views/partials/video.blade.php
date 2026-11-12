@php
    $ytId = null;
    if (! empty($url) && preg_match('~(?:youtu\.be/|youtube(?:-nocookie)?\.com/(?:watch\?(?:[^#]*&)?v=|embed/|shorts/|live/))([\w-]{11})~', $url, $m)) {
        $ytId = $m[1];
    }
@endphp

@if ($ytId)
<style>
    .vid { background: color-mix(in srgb, var(--primary) 38%, #06141f); color: rgba(255, 255, 255, .82); }
    .vid-grid { display: grid; grid-template-columns: 1fr 1.5fr; gap: clamp(2rem, 5vw, 4rem); align-items: center; }
    .vid h2 { color: #fff; }
    .vid .kicker { color: var(--accent-2, #f5b800); }
    .vid-text p { font-size: 1.05rem; max-width: 42ch; }
    .vid-text .btn-outline { color: #fff; border-color: rgba(255, 255, 255, .6); }
    .vid-text .btn-outline:hover { background: #fff; color: var(--ink); border-color: #fff; }

    .vid-frame { aspect-ratio: 16 / 9; overflow: hidden; border-radius: 22px; background: #000; border: 4px solid rgba(255, 255, 255, .14); box-shadow: var(--shadow-lg); }
    .vid-play { position: relative; display: block; width: 100%; height: 100%; }
    .vid-play img { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform .9s var(--ease-out); }
    .vid-play:hover img { transform: scale(1.04); }
    .vid-play::after { content: ""; position: absolute; inset: 0; background: linear-gradient(to top, rgba(6, 20, 36, .45), rgba(6, 20, 36, .1) 60%); }
    .vid-btn {
        position: absolute; z-index: 2; inset: 0; margin: auto; width: 84px; height: 84px; border-radius: 50%;
        display: grid; place-items: center; background: var(--accent-2, #f5b800); color: #3b2f00;
        box-shadow: 0 0 0 0 rgba(245, 184, 0, .55); animation: vid-pulse 2.4s infinite;
        transition: transform .3s var(--ease-out);
    }
    .vid-play:hover .vid-btn { transform: scale(1.08); }
    .vid-btn svg { width: 34px; height: 34px; margin-left: 4px; }
    .vid-frame iframe { display: block; width: 100%; height: 100%; border: 0; }

    @keyframes vid-pulse { 70% { box-shadow: 0 0 0 22px rgba(245, 184, 0, 0); } 100% { box-shadow: 0 0 0 0 rgba(245, 184, 0, 0); } }
    @media (prefers-reduced-motion: reduce) { .vid-btn { animation: none; } }
    @media (max-width: 900px) { .vid-grid { grid-template-columns: 1fr; } }
</style>

<section class="section vid">
    <div class="wrap vid-grid">
        <div class="vid-text" data-reveal>
            <span class="kicker">Profil Sekolah</span>
            <h2>Mengenal {{ $school->name }}</h2>
            <p>Lihat langsung suasana belajar dan kegiatan sehari-hari di sekolah kami.</p>
            <a class="btn btn-outline" href="{{ route('profile', ['school' => $school, 'page' => 'sejarah']) }}">Baca profil lengkap</a>
        </div>

        <div class="vid-frame" data-reveal>
            <a class="vid-play" href="https://www.youtube.com/watch?v={{ $ytId }}" data-yt="{{ $ytId }}"
               target="_blank" rel="noopener" aria-label="Putar video profil {{ $school->name }}">
                <img src="https://i.ytimg.com/vi/{{ $ytId }}/maxresdefault.jpg" alt="" loading="lazy"
                     onload="if (this.naturalWidth < 200) this.src = this.src.replace('maxresdefault', 'hqdefault')"
                     onerror="this.onerror = null; this.src = this.src.replace('maxresdefault', 'hqdefault')">
                <span class="vid-btn" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5.14v13.72a1 1 0 0 0 1.52.85l11-6.86a1 1 0 0 0 0-1.7l-11-6.86A1 1 0 0 0 8 5.14Z"/></svg>
                </span>
            </a>
        </div>
    </div>
</section>

<script>
    (function () {
        document.querySelectorAll('[data-yt]').forEach(function (a) {
            a.addEventListener('click', function (e) {
                e.preventDefault();
                var f = document.createElement('iframe');
                f.src = 'https://www.youtube-nocookie.com/embed/' + a.getAttribute('data-yt') + '?autoplay=1&rel=0&modestbranding=1';
                f.title = a.getAttribute('aria-label') || 'Video';
                f.allow = 'accelerometer; autoplay; encrypted-media; picture-in-picture; fullscreen';
                f.allowFullscreen = true;
                a.parentNode.replaceChild(f, a);
            });
        });
    })();
</script>
@endif