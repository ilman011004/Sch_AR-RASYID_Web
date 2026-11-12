@extends('layouts.site')

@section('title', $title.' — '.$school->name)

@section('content')
<section class="page-title">
    <div class="wrap">
        <p class="crumb"><a href="{{ route('home', $school) }}">Beranda</a> / Profil</p>
        <h1>{{ $title }}</h1>
    </div>
</section>

@if ($page === 'sejarah')
    @php
        $history = trim((string) $school->history);
        $paras = $history !== '' ? array_filter(preg_split('/\R+/', $history), fn ($p) => trim($p) !== '') : [];
        $age = $school->founded_year ? max(now()->year - (int) $school->founded_year, 0) : null;
    @endphp

    <style>
        .hist-stats{display:flex;gap:16px;flex-wrap:wrap;justify-content:center;margin:0 0 36px}
        .hist-badge{flex:1 1 170px;max-width:230px;text-align:center;padding:20px 16px;border-radius:16px;
            background:linear-gradient(135deg,#e8f6ef,#e3f2fa);border:1px solid rgba(0,0,0,.06)}
        .hist-badge strong{display:block;font-size:2rem;line-height:1.1;color:var(--accent,#22a06b)}
        .hist-badge span{font-size:.9rem;color:#5b6770}

        .hist-card{background:#fff;border-radius:18px;padding:32px 36px;border-left:6px solid var(--accent,#22a06b);
            box-shadow:0 8px 30px rgba(20,40,60,.08)}
        .hist-card p{font-size:1.08rem;line-height:1.9;margin:0 0 1em}
        .hist-card p:last-child{margin-bottom:0}

        .hist-title{text-align:center;margin:56px 0 28px}
        .hist-title small{display:block;color:var(--accent,#22a06b);font-weight:700;letter-spacing:.12em;text-transform:uppercase}

        .tl{position:relative;list-style:none;margin:0;padding:0}
        .tl::before{content:"";position:absolute;left:19px;top:0;bottom:0;width:3px;border-radius:3px;
            background:linear-gradient(var(--accent,#22a06b),#9fd3ea)}
        .tl li{position:relative;padding:0 0 32px 60px}
        .tl li:last-child{padding-bottom:0}
        .tl-dot{position:absolute;left:8px;top:6px;width:25px;height:25px;border-radius:50%;background:#fff;
            border:5px solid var(--accent,#22a06b);box-shadow:0 0 0 4px rgba(34,160,107,.15)}
        .tl-year{display:inline-block;font-weight:700;color:#fff;background:var(--accent,#22a06b);
            border-radius:999px;padding:2px 14px;font-size:.9rem;margin-bottom:8px}
        .tl-card{background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 6px 22px rgba(20,40,60,.08);text-align:left}
        .tl-card img{display:block;width:100%;max-height:280px;object-fit:cover}
        .tl-card .tl-text{padding:16px 20px}
        .tl-card h3{margin:0 0 6px;font-size:1.15rem}
        .tl-card p{margin:0;line-height:1.7;color:#4a5560}

        .hist-links{display:flex;gap:12px;flex-wrap:wrap;justify-content:center;margin-top:48px}
        .hist-links a{padding:10px 20px;border-radius:999px;border:2px solid var(--accent,#22a06b);
            color:var(--accent,#22a06b);font-weight:600;text-decoration:none;transition:.2s}
        .hist-links a:hover{background:var(--accent,#22a06b);color:#fff}

        /* Desktop: timeline zig-zag kiri/kanan */
        @media(min-width:900px){
            .tl-wrap{max-width:900px;margin:0 auto}
            .tl::before{left:50%;margin-left:-1.5px}
            .tl li{width:50%;padding:0 48px 36px 0;text-align:right}
            .tl li:nth-child(even){margin-left:50%;padding:0 0 36px 48px;text-align:left}
            .tl-dot{left:auto;right:-12px}
            .tl li:nth-child(even) .tl-dot{right:auto;left:-12px}
        }
        @media(max-width:600px){.hist-card{padding:24px 20px}}
    </style>

    <section class="section">
        <div class="wrap narrow">

            @if ($school->founded_year)
                <div class="hist-stats">
                    <div class="hist-badge"><strong>{{ $school->founded_year }}</strong><span>Tahun berdiri</span></div>
                    @if ($age)
                        <div class="hist-badge"><strong>{{ $age }}+</strong><span>Tahun mengabdi</span></div>
                    @endif
                    @if ($milestones->isNotEmpty())
                        <div class="hist-badge"><strong>{{ $milestones->count() }}</strong><span>Tonggak sejarah</span></div>
                    @endif
                </div>
            @endif

            @if (empty($paras) && $milestones->isEmpty())
                <p class="muted center">Belum diisi.</p>
            @endif
        </div>

        @if ($milestones->isNotEmpty())
            <div class="wrap">
                <div class="hist-title">
                    <small>Perjalanan Kami</small>
                    <h2 style="margin:6px 0 0">Tonggak Sejarah {{ $school->name }}</h2>
                </div>

                <div class="tl-wrap">
                    <ol class="tl">
                        @foreach ($milestones as $m)
                            <li>
                                <span class="tl-dot"></span>
                                <span class="tl-year">{{ $m->year }}</span>
                                <div class="tl-card">
                                    @if ($m->photoUrl())
                                        <img src="{{ $m->photoUrl() }}" alt="{{ $m->title }}" loading="lazy">
                                    @endif
                                    <div class="tl-text">
                                        <h3>{{ $m->title }}</h3>
                                        @if ($m->description)
                                            <p>{{ $m->description }}</p>
                                        @endif
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </div>
        @endif

        <div class="wrap narrow">
            @if (! empty($paras))
                <div class="hist-card" style="margin-top: {{ $milestones->isNotEmpty() ? '56px' : '0' }}">
                    @foreach ($paras as $p)
                        <p>{{ trim($p) }}</p>
                    @endforeach
                </div>
            @endif

            <div class="hist-links">
                <a href="{{ route('profile', ['school' => $school, 'page' => 'visi-misi']) }}">Visi &amp; Misi</a>
                <a href="{{ route('profile', ['school' => $school, 'page' => 'struktur']) }}">Struktur Organisasi</a>
                <a href="{{ route('profile', ['school' => $school, 'page' => 'pegawai']) }}">Pegawai</a>
            </div>
        </div>
    </section>

@elseif ($page === 'visi-misi')
    <section class="section">
        <div class="wrap">
            @include('partials.vm', ['entity' => $school, 'goals' => true])
        </div>
    </section>

@elseif ($page === 'struktur')
    <section class="section">
        <div class="wrap">
            @if ($school->structureImageUrl())
                <a href="{{ $school->structureImageUrl() }}" target="_blank" rel="noopener">
                    <img class="structure-img" src="{{ $school->structureImageUrl() }}" alt="Struktur organisasi {{ $school->name }}">
                </a>
                <p class="muted small center">Klik gambar untuk memperbesar.</p>
            @else
                <p class="muted center">Gambar struktur organisasi belum diunggah.</p>
            @endif
        </div>
    </section>

@else
    {{-- Pegawai --}}
    <section class="section">
        <div class="wrap">
            <div class="org-top">
                @include('site._person', [
                    'name' => $school->head_name,
                    'role' => $school->head_title,
                    'photo' => $school->head_photo,
                ])
            </div>

            @if ($educators->isEmpty() && $support->isEmpty())
                <p class="muted center">Data pegawai belum diisi.</p>
            @endif

            @if ($educators->isNotEmpty())
                <h2 class="center">Tenaga Pendidik</h2>
                <div class="people">
                    @foreach ($educators as $s)
                        @include('site._person', ['name' => $s->name, 'role' => trim($s->position.($s->subject ? ' · '.$s->subject : '')), 'photo' => $s->photo])
                    @endforeach
                </div>
            @endif

            @if ($support->isNotEmpty())
                <h2 class="center">Tenaga Kependidikan</h2>
                <div class="people">
                    @foreach ($support as $s)
                        @include('site._person', ['name' => $s->name, 'role' => trim($s->position.($s->subject ? ' · '.$s->subject : '')), 'photo' => $s->photo])
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endif
@endsection