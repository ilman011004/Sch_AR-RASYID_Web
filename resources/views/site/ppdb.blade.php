@extends('layouts.site')

@section('title', 'Pendaftaran Siswa Baru — '.$school->name)

@section('content')
@php
    // Rapikan teks informasi: nomor -> langkah, "•"/"-" -> poin, baris pendek sebelum daftar -> sub judul.
    $groups = [];
    foreach ($school->ppdbSections() as $s) {
        $items = $s['items'];
        $blocks = [];

        foreach ($items as $i => $line) {
            if (preg_match('/^\d+[\.\)]\s*(.+)$/u', $line, $m)) {
                $type = 'step';
                $text = $m[1];
            } elseif (preg_match('/^[•\-\*]\s*(.+)$/u', $line, $m)) {
                $type = 'bullet';
                $text = $m[1];
            } else {
                $next = $items[$i + 1] ?? '';
                $nextIsList = (bool) preg_match('/^(\d+[\.\)]|[•\-\*])\s*/u', $next);
                $isHeading = $nextIsList && mb_strlen($line) <= 80 && ! str_ends_with($line, '.');
                $type = $isHeading ? 'heading' : 'text';
                $text = $line;
            }

            $last = array_key_last($blocks);
            if (in_array($type, ['step', 'bullet'], true) && $last !== null && $blocks[$last]['type'] === $type) {
                $blocks[$last]['items'][] = $text;
            } else {
                $blocks[] = ['type' => $type, 'items' => [$text]];
            }
        }

        $groups[] = ['title' => $s['title'], 'blocks' => $blocks];
    }
    $poster = $school->ppdbPosterUrl();
@endphp

<section class="page-title">
    <div class="wrap">
        <h1>Pendaftaran Siswa Baru {{ $school->ppdb_year ?: '' }}</h1>
        <p>
            @if ($open)
                <span class="tag tag-ok">Pendaftaran dibuka</span>
                @if ($school->ppdb_end) sampai {{ $school->ppdb_end->translatedFormat('d F Y') }} @endif
            @else
                <span class="tag tag-warn">Pendaftaran ditutup</span>
                @if ($school->ppdb_start && $school->ppdb_start->isFuture())
                    Dibuka mulai {{ $school->ppdb_start->translatedFormat('d F Y') }}
                @endif
            @endif
        </p>
    </div>
</section>

<section class="section">
    <div class="wrap">

        @if (session('error'))<div class="alert err-box">{{ session('error') }}</div>@endif

        <div class="ppdb-layout {{ $poster ? '' : 'no-poster' }}">

            {{-- Kiri: informasi --}}
            <div class="ppdb-main">
                <div class="ppdb-info">
                    <h2>Informasi Pendaftaran</h2>

                    @forelse ($groups as $g)
                        @if ($g['title'])<h3 class="ppdb-h">{{ $g['title'] }}</h3>@endif

                        @foreach ($g['blocks'] as $b)
                            @if ($b['type'] === 'heading')
                                <h4 class="ppdb-sub">{{ $b['items'][0] }}</h4>
                            @elseif ($b['type'] === 'step')
                                <ol class="steps">
                                    @foreach ($b['items'] as $item)<li><span>{{ $item }}</span></li>@endforeach
                                </ol>
                            @elseif ($b['type'] === 'bullet')
                                <ul class="check">
                                    @foreach ($b['items'] as $item)<li>{{ $item }}</li>@endforeach
                                </ul>
                            @else
                                <p>{{ $b['items'][0] }}</p>
                            @endif
                        @endforeach
                    @empty
                        <p class="muted">Informasi pendaftaran belum tersedia.</p>
                    @endforelse
                </div>

                <div class="btns">
                    <a class="btn btn-outline" href="{{ route('ppdb.status', $school) }}">Cek status pendaftaran</a>
                    @if ($school->whatsapp)
                        <a class="btn btn-outline" href="https://wa.me/{{ $school->whatsapp }}" target="_blank" rel="noopener">Tanya lewat WhatsApp</a>
                    @endif
                </div>
            </div>

            {{-- Kanan: poster + tombol daftar --}}
            <aside class="ppdb-side">
                @if ($poster)
                    <figure class="ppdb-poster">
                        <a href="{{ $poster }}" target="_blank" rel="noopener">
                            <img src="{{ $poster }}" alt="Poster PPDB {{ $school->name }}">
                        </a>
                    </figure>
                @endif

                <div class="ppdb-cta">
                    @if (! $open)
                        <p class="muted">Formulir akan tersedia saat pendaftaran dibuka.</p>
                    @else
                        @auth
                            @if (auth()->user()->isParent())
                                <p>Halo, <strong>{{ auth()->user()->name }}</strong></p>
                                <a class="btn" href="{{ route('ppdb.form', $school) }}">Isi formulir pendaftaran</a>
                            @else
                                <p class="muted">Anda masuk sebagai admin. Untuk mendaftar, gunakan akun orang tua / wali.</p>
                            @endif
                        @else
                            <a class="btn" href="{{ route('ppdb.form', $school) }}">Daftar sekarang</a>
                            <p class="small muted" style="margin-top:.8rem">
                                Belum punya akun? <a href="{{ route('ppdb.register', $school) }}">Buat akun</a>
                            </p>
                        @endauth
                    @endif
                </div>
            </aside>
        </div>
    </div>
</section>
@endsection