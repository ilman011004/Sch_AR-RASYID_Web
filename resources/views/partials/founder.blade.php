{{-- Pendiri (kiri, permanen di kode) dan Ketua Yayasan (kanan, dari database/pengaturan admin). Variabel: $foundation --}}
@php
    /*
     * PENDIRI: permanen, isi langsung di sini.
     * - photo : taruh file di  public/images/founder/pendiri.jpg  (atau ubah nama di bawah).
     * - pos   : posisi foto di dalam kotak. Format 'center Y%'.
     *           0%   = foto mulai dari tepi atas (kepala tidak terpotong).
     *           Makin besar (misal 10%) = foto bergeser ke atas, bagian atas kepala mulai terpotong.
     *
     * KETUA YAYASAN: JANGAN diisi di sini. Semua datanya (nama, jabatan, foto, sambutan)
     * otomatis dari database lewat pengaturan admin, jadi bisa diganti kapan saja.
     */
    $pendiri = [
        'label' => 'Pendiri',
        'name'  => 'Dr.H.Mad Moerad Baso, M.Sc., Ph.D',
        'role'  => 'Pendiri Yayasan AR-Rasyid',
        'photo' => asset('images/founder/Pendiri.png'),
        'pos'   => 'center 0%',
        'text'  => 'Membangun Tanah Bumbu bukan hanya soal mengolah kekayaan alamnya, tetapi menyiapkan manusianya. Melalui Ar-Rasyid, kita memadukan kecerdasan intelektual dan kekuatan akhlak agar putra-putri daerah mampu berdiri tegak di tanahnya sendiri.',
    ];

    $ketua = [
        'label' => 'Ketua Yayasan',
        'name'  => $foundation->head_name ?: 'Ketua Yayasan',
        'role'  => $foundation->head_title ?: 'Ketua Yayasan',
        'photo' => $foundation->headPhotoUrl(),
        'pos'   => 'center 0%',
        'text'  => (string) $foundation->welcome_message,
    ];

    $people = [
        ['key' => 'pendiri', 'data' => $pendiri],
        ['key' => 'ketua', 'data' => $ketua],
    ];
@endphp

<style>
    .pk { background: color-mix(in srgb, var(--primary) 38%, #06141f); --pk-offset: 64px; }
    .pk .sec-head h2 { color: #fff; }
    .pk .sec-head p { color: rgba(255, 255, 255, .75); }
    .pk .kicker { color: var(--accent-2, #f5b800); }
    .pk .sec-head h2::after { background: var(--accent-2, #f5b800); }

    .pk-grid { display: grid; gap: 1.5rem; grid-template-columns: repeat(2, 1fr); align-items: start; }

    /* Tata letak bertingkat: Pendiri di atas, Ketua turun */
    .pk-card.pendiri { margin-top: 0; }
    .pk-card.ketua { margin-top: var(--pk-offset); }

    .pk-card { display: flex; flex-direction: column; overflow: hidden; border-radius: 20px; background: #0d2236; box-shadow: 0 18px 40px rgba(0, 0, 0, .35); }

    .pk-photo { position: relative; flex: none; height: 470px; overflow: hidden; background: #0d2236; }
    .pk-photo img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; transition: transform .6s ease; }
    .pk-card:hover .pk-photo img { transform: scale(1.03); }
    .pk-photo::after { content: ""; position: absolute; inset: 0; pointer-events: none; background: linear-gradient(to top, #0d2236 0%, rgba(13, 34, 54, 0) 16%); }

    .pk-avatar { position: absolute; inset: 0; display: none; place-items: center; font-size: 7rem; font-weight: 800; color: rgba(255, 255, 255, .85); background: color-mix(in srgb, var(--primary) 55%, #06141f); }
    .pk-avatar.show { display: grid; }

    .pk-label { position: absolute; z-index: 2; top: 1.1rem; left: 1.1rem; padding: .28rem .95rem; border-radius: 999px; font-size: .78rem; font-weight: 800; background: var(--accent-2, #f5b800); color: #3b2f00; }
    .pk-card.ketua .pk-label { background: var(--accent, #22a06b); color: #fff; }

    .pk-info { flex: 1; display: flex; flex-direction: column; padding: .6rem 1.7rem 1.5rem; min-height: 250px; color: #fff; }
    .pk-info h3 { color: #fff; font-size: clamp(1.25rem, 2.2vw, 1.6rem); margin: 0 0 .15rem; line-height: 1.25; min-height: 2.5em; display: flex; align-items: flex-end; }
    .pk-role { margin: 0 0 .9rem; font-weight: 700; font-size: .92rem; color: color-mix(in srgb, var(--accent, #22a06b) 55%, #fff); }
    .pk-text { margin: 0; padding-top: .9rem; border-top: 1px solid rgba(255, 255, 255, .28); font-size: .95rem; line-height: 1.6; color: rgba(255, 255, 255, .88); display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }

    .pk-more-slot { margin-top: auto; padding-top: .7rem; min-height: 2rem; }
    .pk-more { padding: 0; border: 0; background: none; cursor: pointer; font: inherit; font-weight: 700; font-size: .9rem; color: var(--accent-2, #f5b800); text-decoration: underline; text-underline-offset: 3px; }

    .pk-dialog { width: min(640px, 100% - 2rem); max-height: 85vh; overflow: auto; border: 0; border-radius: 20px; padding: 2rem 2rem 1.6rem; color: var(--text); box-shadow: 0 30px 80px rgba(0, 0, 0, .4); }
    .pk-dialog::backdrop { background: rgba(6, 20, 36, .65); }
    .pk-dialog h3 { margin: 0 0 .2rem; }
    .pk-dialog .pk-dialog-role { color: var(--accent, #22a06b); font-weight: 700; margin: 0 0 1.2rem; }
    .pk-dialog .pk-dialog-msg { font-size: 1.02rem; }
    .pk-dialog form { margin: 1.4rem 0 0; text-align: right; }

    @media (max-width: 800px) {
        .pk-grid { grid-template-columns: 1fr; }
        .pk-card.ketua { margin-top: 0; }
        .pk-photo { height: 420px; }
        .pk-info { min-height: 0; }
        .pk-info h3 { min-height: 0; }
    }
</style>

<section class="section pk" id="pendiri">
    <div class="wrap">
        <div class="sec-head">
            <span class="kicker">Pimpinan Yayasan</span>
            <h2>Pendiri dan Ketua Yayasan</h2>
        </div>

        <div class="pk-grid">
            @foreach ($people as $p)
                @php $f = $p['data']; @endphp
                <article class="pk-card {{ $p['key'] }}">
                    <div class="pk-photo">
                        @if (! empty($f['photo']))
                            <img src="{{ $f['photo'] }}" alt="{{ $f['name'] }}" loading="lazy"
                                 style="object-position: {{ $f['pos'] ?? 'center top' }};"
                                 onerror="this.style.display='none'; this.nextElementSibling.classList.add('show');">
                        @endif
                        <div class="pk-avatar {{ empty($f['photo']) ? 'show' : '' }}" aria-hidden="true">{{ mb_substr(preg_replace('/^\[[^\]]*\]\s*/u', '', $f['name']), 0, 1) }}</div>
                        <span class="pk-label">{{ $f['label'] }}</span>
                    </div>

                    <div class="pk-info">
                        <h3>{{ $f['name'] }}</h3>
                        <p class="pk-role">{{ $f['role'] }}</p>
                        @if (! empty($f['text']))
                            <p class="pk-text">{{ $f['text'] }}</p>
                        @endif
                        <div class="pk-more-slot">
                            @if (mb_strlen($f['text'] ?? '') > 150)
                                <button type="button" class="pk-more" data-pk-open="pk-dialog-{{ $p['key'] }}">
                                    {{ $p['key'] === 'ketua' ? 'Baca sambutan lengkap' : 'Baca selengkapnya' }}
                                </button>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>

@foreach ($people as $p)
    @php $f = $p['data']; @endphp
    @if (! empty($f['text']))
    <dialog class="pk-dialog" id="pk-dialog-{{ $p['key'] }}">
        <h3>{{ $p['key'] === 'ketua' ? 'Sambutan ' . $f['role'] : 'Pesan ' . $f['label'] }}</h3>
        <p class="pk-dialog-role">{{ $f['name'] }}</p>
        <div class="pk-dialog-msg">{!! nl2br(e($f['text'])) !!}</div>
        <form method="dialog"><button class="btn btn-sm" type="submit">Tutup</button></form>
    </dialog>
    @endif
@endforeach

<script>
    (function () {
        document.querySelectorAll('[data-pk-open]').forEach(function (b) {
            b.addEventListener('click', function () {
                var d = document.getElementById(b.getAttribute('data-pk-open'));
                if (d && d.showModal) d.showModal();
            });
        });
        document.querySelectorAll('.pk-dialog').forEach(function (d) {
            d.addEventListener('click', function (e) { if (e.target === d) d.close(); });
        });
    })();
</script>