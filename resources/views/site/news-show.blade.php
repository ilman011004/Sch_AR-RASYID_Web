@extends('layouts.site')

@php
    // Dikirim dari controller: berita lain dan pengumuman lain (tanpa tulisan yang sedang dibuka)
    $otherNews = $otherNews ?? collect();
    $announcements = $announcements ?? collect();

    $shareUrl = url()->current();
    $isAnnouncement = $post->type === 'pengumuman';

    // Estimasi waktu baca (±200 kata/menit) dan pemecahan isi menjadi paragraf
    $words = count(preg_split('/\s+/u', trim(strip_tags((string) $post->body)), -1, PREG_SPLIT_NO_EMPTY));
    $readMinutes = max(1, (int) ceil($words / 200));
    $paragraphs = preg_split('/\R{2,}/u', trim((string) $post->body), -1, PREG_SPLIT_NO_EMPTY);

    $shareLinks = [
        'WhatsApp' => 'https://wa.me/?text='.rawurlencode($post->title."\n".$shareUrl),
        'Facebook' => 'https://www.facebook.com/sharer/sharer.php?u='.rawurlencode($shareUrl),
        'X'        => 'https://twitter.com/intent/tweet?text='.rawurlencode($post->title).'&url='.rawurlencode($shareUrl),
    ];

    // Data terstruktur agar tampil rapi di Google
    $ld = [
        '@context' => 'https://schema.org',
        '@type' => 'NewsArticle',
        'headline' => \Illuminate\Support\Str::limit($post->title, 110, ''),
        'datePublished' => $post->published_at?->toIso8601String(),
        'dateModified' => ($post->updated_at ?? $post->published_at)?->toIso8601String(),
        'mainEntityOfPage' => $shareUrl,
        'author' => ['@type' => 'Organization', 'name' => $school->name],
        'publisher' => ['@type' => 'Organization', 'name' => $school->name],
    ];
    if ($post->image) {
        $ld['image'] = [asset('storage/'.$post->image)];
    }
@endphp

@section('title', $post->title.' — '.$school->name)
@section('description', $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->body), 150))

@section('content')
<style>
    .nw { padding-block: clamp(1.5rem, 3vw, 2.5rem) clamp(3rem, 6vw, 5rem); }
    .nw-layout { display: grid; grid-template-columns: minmax(0, 1fr) 340px; gap: clamp(2rem, 4vw, 3.5rem); align-items: start; }

    /* Breadcrumb */
    .nw-crumbs { display: flex; flex-wrap: wrap; align-items: center; gap: .5rem; margin-bottom: 1.5rem; font-size: .85rem; color: var(--muted); }
    .nw-crumbs a { color: var(--muted); text-decoration: none; }
    .nw-crumbs a:hover { color: var(--ink); }
    .nw-crumbs [aria-current] { color: var(--ink); font-weight: 600; }

    /* Kepala artikel */
    .nw-tag {
        display: inline-block; padding: .3rem .8rem; border-radius: 6px; text-decoration: none;
        font-size: .72rem; font-weight: 800; letter-spacing: .12em; text-transform: uppercase;
        color: #fff; background: var(--ink);
    }
    .nw-tag.is-warn { background: #b45309; }
    .nw-title { margin: 1rem 0 0; color: var(--ink); font-size: clamp(1.9rem, 3.6vw, 2.9rem); line-height: 1.18; letter-spacing: -.02em; font-weight: 800; text-wrap: balance; }
    .nw-lead { margin: 1rem 0 0; font-size: clamp(1.05rem, 1.6vw, 1.25rem); line-height: 1.65; color: var(--muted); }

    .nw-byline { display: flex; align-items: center; gap: .9rem; margin-top: 1.6rem; padding-top: 1.3rem; border-top: 1px solid var(--line); }
    .nw-avatar { flex: none; width: 46px; height: 46px; border-radius: 50%; object-fit: cover; background: color-mix(in srgb, var(--accent-2, #f5b800) 25%, #fff); display: grid; place-items: center; font-weight: 800; color: var(--ink); }
    .nw-by strong { display: block; color: var(--ink); font-size: .98rem; line-height: 1.3; }
    .nw-by span { display: block; margin-top: .1rem; font-size: .84rem; color: var(--muted); }

    /* Tombol bagikan */
    .nw-share { display: flex; flex-wrap: wrap; align-items: center; gap: .5rem; margin-top: 1.1rem; }
    .nw-share-label { margin-right: .3rem; font-size: .8rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: var(--muted); }
    .nw-share a, .nw-share button {
        padding: .45rem .95rem; border-radius: 999px; cursor: pointer; text-decoration: none; font: inherit; font-size: .84rem; font-weight: 700;
        color: var(--ink); background: #fff; border: 1px solid var(--line); transition: background .2s, color .2s, border-color .2s;
    }
    .nw-share a:hover, .nw-share button:hover { color: #fff; border-color: transparent; background: var(--ink); }
    .nw-share a[data-net="WhatsApp"]:hover { background: #1faa59; }
    .nw-share a[data-net="Facebook"]:hover { background: #1877f2; }
    .nw-share a[data-net="X"]:hover { background: #111; }

    /* Gambar utama: poster tegak tidak dipotong */
    .nw-figure { display: block; margin: 1.8rem 0 0; border-radius: 16px; overflow: hidden; background: color-mix(in srgb, var(--ink) 10%, #f3f5f7); }
    .nw-figure img { display: block; width: 100%; height: auto; max-height: 580px; object-fit: contain; margin: 0 auto; }

    /* Isi tulisan */
    .nw-prose { margin-top: 2rem; font-size: 1.08rem; line-height: 1.9; color: var(--text); overflow-wrap: anywhere; }
    .nw-prose p { margin: 0 0 1.35em; }
    .nw-prose p:first-child { font-size: 1.16rem; color: var(--ink); }
    .nw-prose p:first-child::first-letter { font-weight: 800; }

    .nw-foot { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; margin-top: 2.4rem; padding-top: 1.4rem; border-top: 1px solid var(--line); }
    .nw-foot .nw-share { margin-top: 0; }
    .nw-back { font-weight: 700; text-decoration: none; color: var(--ink); }
    .nw-back:hover { text-decoration: underline; }

    /* Sidebar */
    .nw-side { display: grid; gap: 1.6rem; }
    .nw-widget { background: #fff; border: 1px solid var(--line); border-radius: 16px; overflow: hidden; }
    .nw-w-title { margin: 0; padding: 1.1rem 1.3rem; display: flex; align-items: center; gap: .7rem; font-size: 1rem; font-weight: 800; letter-spacing: .02em; color: var(--ink); border-bottom: 1px solid var(--line); }
    .nw-w-title::before { content: ""; width: 5px; height: 1.15em; border-radius: 3px; background: var(--accent-2, #f5b800); }
    .nw-widget.is-warn .nw-w-title::before { background: #b45309; }
    .nw-w-list { display: grid; }
    .nw-w-more { display: block; padding: .95rem 1.3rem; text-align: center; font-size: .88rem; font-weight: 700; text-decoration: none; color: var(--ink); border-top: 1px solid var(--line); background: color-mix(in srgb, var(--ink) 4%, #fff); }
    .nw-w-more:hover { background: color-mix(in srgb, var(--ink) 9%, #fff); }

    .nw-item { display: flex; gap: .9rem; padding: 1rem 1.3rem; text-decoration: none; color: inherit; }
    .nw-item + .nw-item { border-top: 1px solid var(--line); }
    .nw-item:hover .nw-item-title { color: var(--primary); text-decoration: underline; }
    .nw-thumb { flex: none; width: 92px; height: 68px; border-radius: 10px; object-fit: cover; background: #e8edf1; }
    .nw-item-title { display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; font-size: .93rem; line-height: 1.4; font-weight: 700; color: var(--ink); }
    .nw-item-meta { margin-top: .35rem; font-size: .78rem; color: var(--muted); }

    /* Berita pertama tampil besar */
    .nw-feat { display: block; padding: 1.1rem 1.3rem; text-decoration: none; color: inherit; }
    .nw-feat:hover .nw-item-title { color: var(--primary); text-decoration: underline; }
    .nw-feat img { display: block; width: 100%; aspect-ratio: 16 / 9; object-fit: cover; border-radius: 12px; background: #e8edf1; }
    .nw-feat .nw-item-title { margin-top: .8rem; font-size: 1.05rem; -webkit-line-clamp: 3; }
    .nw-feat + .nw-item { border-top: 1px solid var(--line); }

    /* Pengumuman: blok tanggal di kiri */
    .nw-date { flex: none; width: 56px; height: 60px; border-radius: 12px; display: grid; place-content: center; text-align: center; background: color-mix(in srgb, #b45309 12%, #fff); color: #92400e; }
    .nw-date b { display: block; font-size: 1.35rem; line-height: 1; font-weight: 800; }
    .nw-date small { display: block; margin-top: .2rem; font-size: .68rem; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; }

    @media (max-width: 960px) {
        .nw-layout { grid-template-columns: 1fr; }
        .nw-prose { font-size: 1.03rem; line-height: 1.85; }
    }
</style>

<script type="application/ld+json">{!! json_encode($ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>

<section class="section nw">
    <div class="wrap">

        <nav class="nw-crumbs" aria-label="Breadcrumb">
            <a href="{{ route('home', $school) }}">Beranda</a><span>›</span>
            <a href="{{ route('news', $school) }}">Berita</a><span>›</span>
            <span aria-current="page">{{ \Illuminate\Support\Str::limit($post->title, 60) }}</span>
        </nav>

        <div class="nw-layout">

            {{-- ============ Artikel ============ --}}
            <article>
                <header>
                    <div style="display:flex; align-items:center; gap:.5rem; flex-wrap:wrap;">
                        <a class="nw-tag {{ $isAnnouncement ? 'is-warn' : '' }}" href="{{ route('news', ['school' => $school, 'jenis' => $post->type]) }}">{{ $post->typeLabel() }}</a>
                        @if ($post->school && $post->school->isFoundation())
                            <span class="nw-tag" style="background: var(--accent, #22a06b);">Yayasan</span>
                        @endif
                    </div>
                    <h1 class="nw-title">{{ $post->title }}</h1>
                    @if ($post->excerpt)
                        <p class="nw-lead">{{ $post->excerpt }}</p>
                    @endif

                    <div class="nw-byline">
                        @if ($post->school && $post->school->isFoundation())
                            @if ($post->school->logoUrl())
                                <img class="nw-avatar" src="{{ $post->school->logoUrl() }}" alt="">
                            @else
                                <span class="nw-avatar" aria-hidden="true">Y</span>
                            @endif
                            <div class="nw-by">
                                <strong>Redaksi {{ $post->school->name }}</strong>
                                <span>{{ $post->published_at?->translatedFormat('l, d F Y') }} · {{ $readMinutes }} menit baca</span>
                            </div>
                        @else
                            @if ($school->logoUrl())
                                <img class="nw-avatar" src="{{ $school->logoUrl() }}" alt="">
                            @else
                                <span class="nw-avatar" aria-hidden="true">{{ mb_substr($school->name, 0, 1) }}</span>
                            @endif
                            <div class="nw-by">
                                <strong>Redaksi {{ $school->name }}</strong>
                                <span>{{ $post->published_at?->translatedFormat('l, d F Y') }} · {{ $readMinutes }} menit baca</span>
                            </div>
                        @endif
                    </div>

                    <div class="nw-share">
                        <span class="nw-share-label">Bagikan</span>
                        @foreach ($shareLinks as $name => $link)
                            <a href="{{ $link }}" target="_blank" rel="noopener" data-net="{{ $name }}">{{ $name }}</a>
                        @endforeach
                        <button type="button" data-nw-copy="{{ $shareUrl }}">Salin tautan</button>
                    </div>
                </header>

                @if ($post->image)
                    <a class="nw-figure" href="{{ asset('storage/'.$post->image) }}" target="_blank" rel="noopener" title="Buka gambar penuh">
                        <img src="{{ asset('storage/'.$post->image) }}" alt="{{ $post->title }}">
                    </a>
                @endif

                <div class="nw-prose">
                    @foreach ($paragraphs as $para)
                        <p>{!! nl2br(e(trim($para))) !!}</p>
                    @endforeach
                </div>

                <footer class="nw-foot">
                    <a class="nw-back" href="{{ route('news', $school) }}">← Semua berita & pengumuman</a>
                    <div class="nw-share">
                        <span class="nw-share-label">Bagikan</span>
                        @foreach ($shareLinks as $name => $link)
                            <a href="{{ $link }}" target="_blank" rel="noopener" data-net="{{ $name }}">{{ $name }}</a>
                        @endforeach
                        <button type="button" data-nw-copy="{{ $shareUrl }}">Salin tautan</button>
                    </div>
                </footer>
            </article>

            {{-- ============ Samping: Berita Lainnya, lalu Pengumuman Lainnya ============ --}}
            <aside class="nw-side">

                @if ($otherNews->isNotEmpty())
                    <section class="nw-widget">
                        <h3 class="nw-w-title">Berita Lainnya</h3>
                        <div class="nw-w-list">
                            @foreach ($otherNews as $item)
                                @php $itemUrl = route('news.show', ['school' => $school, 'slug' => $item->slug]); @endphp

                                @if ($loop->first && $item->image)
                                    <a class="nw-feat" href="{{ $itemUrl }}">
                                        <img src="{{ asset('storage/'.$item->image) }}" alt="" loading="lazy">
                                        <div class="nw-item-title">{{ $item->title }}</div>
                                        <div class="nw-item-meta">{{ $item->published_at?->translatedFormat('d F Y') }}</div>
                                    </a>
                                @else
                                    <a class="nw-item" href="{{ $itemUrl }}">
                                        @if ($item->image)
                                            <img class="nw-thumb" src="{{ asset('storage/'.$item->image) }}" alt="" loading="lazy">
                                        @endif
                                        <div>
                                            <div class="nw-item-title">{{ $item->title }}</div>
                                            <div class="nw-item-meta">{{ $item->published_at?->translatedFormat('d F Y') }}</div>
                                        </div>
                                    </a>
                                @endif
                            @endforeach
                        </div>
                        <a class="nw-w-more" href="{{ route('news', ['school' => $school, 'jenis' => 'berita']) }}">Semua berita →</a>
                    </section>
                @endif

                @if ($announcements->isNotEmpty())
                    <section class="nw-widget is-warn">
                        <h3 class="nw-w-title">Pengumuman Lainnya</h3>
                        <div class="nw-w-list">
                            @foreach ($announcements as $item)
                                <a class="nw-item" href="{{ route('news.show', ['school' => $school, 'slug' => $item->slug]) }}">
                                    <div class="nw-date" aria-hidden="true">
                                        <b>{{ $item->published_at?->translatedFormat('d') }}</b>
                                        <small>{{ $item->published_at?->translatedFormat('M') }}</small>
                                    </div>
                                    <div>
                                        <div class="nw-item-title">{{ $item->title }}</div>
                                        <div class="nw-item-meta">{{ $item->published_at?->translatedFormat('d F Y') }}</div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                        <a class="nw-w-more" href="{{ route('news', ['school' => $school, 'jenis' => 'pengumuman']) }}">Semua pengumuman →</a>
                    </section>
                @endif

            </aside>
        </div>
    </div>
</section>

<script>
    (function () {
        document.querySelectorAll('[data-nw-copy]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var url = btn.getAttribute('data-nw-copy');
                var label = btn.textContent;
                var done = function () {
                    btn.textContent = 'Tautan disalin ✓';
                    setTimeout(function () { btn.textContent = label; }, 1800);
                };
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(url).then(done, function () { window.prompt('Salin tautan ini:', url); });
                } else {
                    window.prompt('Salin tautan ini:', url);
                }
            });
        });
    })();
</script>
@endsection