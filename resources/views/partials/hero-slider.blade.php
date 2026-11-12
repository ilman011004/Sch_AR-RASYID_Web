{{-- Latar hero berupa slider foto/video/YouTube. Variabel: $slides. Kosong = tidak menampilkan apa pun. --}}
@if ($slides->isNotEmpty())
<style>
    .hero:has(.hbg) { min-height: clamp(620px, 100svh, 880px); display: flex; align-items: center; background: var(--primary); }
    .hero:has(.hbg) .hero-grid { grid-template-columns: 1fr; width: min(1160px, 100% - 2.5rem); }
    .hero:has(.hbg) .hero-text { position: relative; z-index: 2; max-width: 720px; text-shadow: 0 2px 16px rgba(0, 0, 0, .35); }
    .hero:has(.hbg) .hero-text h1,
    .hero:has(.hbg) .hero-text .lead { color: #fff !important; }
    .hero:has(.hbg) .btn { text-shadow: none; }

    .hbg { position: absolute; inset: 0; z-index: 1; overflow: hidden; background: var(--primary); }
    .hbg-slide { position: absolute; inset: 0; opacity: 0; transition: opacity 1s ease; }
    .hbg-slide.on { opacity: 1; }
    .hbg-slide img, .hbg-slide video { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; display: block; }
    .hbg-slide img { transform: scale(1); transition: transform 8s ease-out; }
    .hbg-slide.on img { transform: scale(1.07); }

    /* Thumbnail cadangan untuk slide YouTube: tanpa efek zoom */
    .hbg-slide img.yt-thumb { transform: none; transition: none; }
    .hbg-slide.on img.yt-thumb { transform: none; }

    /* iframe YouTube dibuat "cover" seperti object-fit: cover */
    .hbg-slide iframe {
        position: absolute; top: 50%; left: 50%; border: 0; pointer-events: none;
        width: max(100%, 177.78vh); height: max(100%, 56.25vw);
        transform: translate(-50%, -50%);
    }

    /* Gelapkan foto secara rata (tanpa gradasi) agar teks dan navbar terbaca.
       Ubah angka 50% di bawah: makin besar = makin gelap, makin kecil = makin terang. */
    .hbg::after {
        content: ""; position: absolute; inset: 0; pointer-events: none;
        background: color-mix(in srgb, color-mix(in srgb, var(--primary) 40%, #000) 50%, transparent);
    }

    .hbg-btn {
        position: absolute; z-index: 3; top: 50%; transform: translateY(-50%); width: 46px; height: 46px; border-radius: 50%;
        border: 1px solid rgba(255, 255, 255, .4); background: rgba(255, 255, 255, .16); color: #fff; font-size: 1.7rem; line-height: 1;
        cursor: pointer; backdrop-filter: blur(4px); display: grid; place-items: center; transition: background .2s;
    }
    .hbg-btn:hover { background: rgba(255, 255, 255, .32); }
    .hbg-btn.prev { left: 1.2rem; }
    .hbg-btn.next { right: 1.2rem; }

    .hbg-dots { position: absolute; z-index: 3; left: 0; right: 0; bottom: 5rem; display: flex; justify-content: center; gap: .5rem; }
    .hbg-dots button { width: 10px; height: 10px; padding: 0; border: 0; border-radius: 999px; cursor: pointer; background: rgba(255, 255, 255, .5); transition: width .25s, background .25s; }
    .hbg-dots button.on { width: 30px; background: #fff; }

    .hbg-cap { position: absolute; z-index: 3; right: 1.6rem; bottom: 5rem; max-width: 320px; text-align: right; color: #fff; text-shadow: 0 2px 12px rgba(0, 0, 0, .5); display: none; }
    .hbg-cap.on { display: block; }
    .hbg-cap b { display: block; font-size: 1rem; }
    .hbg-cap span { font-size: .85rem; opacity: .9; }

    @media (max-width: 1000px) {
        .hero:has(.hbg) { min-height: 560px; }
        .hbg-btn { display: none; }
        .hbg-cap { display: none !important; }
    }
    @media (prefers-reduced-motion: reduce) {
        .hbg-slide, .hbg-slide img { transition: none; }
        .hbg-slide.on img { transform: none; }
    }
</style>

<div class="hbg" data-hbg>
    @foreach ($slides as $s)
        @php
            $yt     = method_exists($s, 'youtubeId') ? $s->youtubeId() : null;
            $stype  = $s->type ?? null;
            $svid   = $s->video ?? null;
            $stitle = $s->title ?? null;
            $scap   = $s->caption ?? null;
            $simg   = $s->image ?? null;
        @endphp
        <div class="hbg-slide {{ $loop->first ? 'on' : '' }}">
            @if ($yt)
                {{-- Gambar cadangan: tampil sampai video YouTube termuat. Kalau foto diunggah, foto itu yang dipakai. --}}
                @if ($simg)
                    <img class="yt-thumb" src="{{ asset('storage/'.$simg) }}" alt="" aria-hidden="true">
                @else
                    <img class="yt-thumb" src="https://i.ytimg.com/vi/{{ $yt }}/hqdefault.jpg" alt="" aria-hidden="true">
                @endif

                <iframe data-yt
                        data-src="https://www.youtube-nocookie.com/embed/{{ $yt }}?autoplay=1&mute=1&controls=0&playsinline=1&rel=0&modestbranding=1&enablejsapi=1{{ $slides->count() === 1 ? '&loop=1&playlist='.$yt : '' }}"
                        allow="autoplay; encrypted-media"
                        referrerpolicy="strict-origin-when-cross-origin"
                        title="{{ $stitle ?: 'Video '.$loop->iteration }}"></iframe>
            @elseif ($stype === 'video' && $svid)
                <video muted playsinline preload="{{ $loop->first ? 'auto' : 'metadata' }}"
                       @if ($simg) poster="{{ asset('storage/'.$simg) }}" @endif
                       @if ($slides->count() === 1) loop @endif>
                    <source src="{{ asset('storage/'.$svid) }}">
                </video>
            @elseif ($simg)
                <img src="{{ asset('storage/'.$simg) }}" alt="{{ $stitle ?: ($scap ?: 'Foto '.$loop->iteration) }}"
                     @if (! $loop->first) loading="lazy" @endif>
            @endif
        </div>
    @endforeach

    @foreach ($slides as $s)
        @php $ct = $s->title ?? null; $cc = $s->caption ?? null; @endphp
        @if ($ct || $cc)
            <div class="hbg-cap {{ $loop->first ? 'on' : '' }}" data-cap>
                @if ($ct)<b>{{ $ct }}</b>@endif
                @if ($cc)<span>{{ $cc }}</span>@endif
            </div>
        @else
            <div class="hbg-cap" data-cap></div>
        @endif
    @endforeach

    @if ($slides->count() > 1)
        <button type="button" class="hbg-btn prev" data-prev aria-label="Sebelumnya">‹</button>
        <button type="button" class="hbg-btn next" data-next aria-label="Berikutnya">›</button>
        <div class="hbg-dots">
            @foreach ($slides as $s)
                <button type="button" class="{{ $loop->first ? 'on' : '' }}" aria-label="Slide {{ $loop->iteration }}"></button>
            @endforeach
        </div>
    @endif
</div>

<script>
    (function () {
        document.querySelectorAll('[data-hbg]').forEach(function (root) {
            var slides = [].slice.call(root.querySelectorAll('.hbg-slide'));
            var caps = [].slice.call(root.querySelectorAll('[data-cap]'));
            var dots = [].slice.call(root.querySelectorAll('.hbg-dots button'));
            var n = slides.length, i = 0, timer = null;

            function schedule() {
                clearTimeout(timer);
                if (n < 2) return;
                // Video/YouTube: pindah saat selesai (cadangan 45 detik). Foto: 5,5 detik.
                var hasMedia = slides[i].querySelector('video, [data-yt]');
                timer = setTimeout(next, hasMedia ? 45000 : 5500);
            }

            function show(k) {
                i = (k + n) % n;
                slides.forEach(function (s, j) {
                    s.classList.toggle('on', j === i);

                    var f = s.querySelector('[data-yt]');
                    if (f) {
                        if (j === i) { if (!f.getAttribute('src')) f.setAttribute('src', f.dataset.src); }
                        else f.removeAttribute('src'); // hentikan video yang tidak tampil
                        return;
                    }

                    var v = s.querySelector('video');
                    if (!v) return;
                    if (j === i) {
                        v.currentTime = 0;
                        var p = v.play();
                        if (p && p.catch) p.catch(function () {});
                    } else {
                        v.pause();
                    }
                });
                caps.forEach(function (c, j) { c.classList.toggle('on', j === i && c.textContent.trim() !== ''); });
                dots.forEach(function (d, j) { d.classList.toggle('on', j === i); });
                schedule();
            }

            function next() { show(i + 1); }

            slides.forEach(function (s) {
                var v = s.querySelector('video');
                if (v && n > 1) v.addEventListener('ended', next);

                var f = s.querySelector('[data-yt]');
                if (f && n > 1) f.addEventListener('load', function () {
                    // minta YouTube mengirim event status pemutaran
                    f.contentWindow.postMessage(JSON.stringify({ event: 'listening' }), '*');
                });
            });

            // YouTube selesai diputar (state 0) -> slide berikutnya
            window.addEventListener('message', function (e) {
                if (String(e.origin).indexOf('youtube') < 0) return;
                var d; try { d = JSON.parse(e.data); } catch (_) { return; }
                var f = slides[i].querySelector('[data-yt]');
                if (d && d.event === 'onStateChange' && d.info === 0 && f && e.source === f.contentWindow) next();
            });

            var prev = root.querySelector('[data-prev]');
            var nxt = root.querySelector('[data-next]');
            if (prev) prev.addEventListener('click', function () { show(i - 1); });
            if (nxt) nxt.addEventListener('click', function () { show(i + 1); });
            dots.forEach(function (d, j) { d.addEventListener('click', function () { show(j); }); });

            var x0 = null;
            root.addEventListener('touchstart', function (e) { x0 = e.touches[0].clientX; }, { passive: true });
            root.addEventListener('touchend', function (e) {
                if (x0 === null) return;
                var dx = e.changedTouches[0].clientX - x0;
                if (Math.abs(dx) > 50) show(dx < 0 ? i + 1 : i - 1);
                x0 = null;
            });

            show(0);
        });
    })();
</script>
@endif