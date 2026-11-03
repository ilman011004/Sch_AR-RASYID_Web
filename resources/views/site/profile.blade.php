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
    <section class="section">
        <div class="wrap narrow">
            @if ($school->founded_year)
                <p class="muted">Berdiri sejak tahun {{ $school->founded_year }}</p>
            @endif
            @if ($school->history)
                <div class="prose">{!! nl2br(e($school->history)) !!}</div>
            @else
                <p class="muted">Belum diisi.</p>
            @endif
        </div>
    </section>

@elseif ($page === 'visi-misi')
    <section class="section">
        <div class="wrap narrow">
            <h2>Visi</h2>
            <p class="lead">{{ $school->vision ?: '—' }}</p>

            <h2>Misi</h2>
            <ul class="check">
                @forelse ($school->missionList() as $m)
                    <li>{{ $m }}</li>
                @empty
                    <li class="muted">Belum diisi.</li>
                @endforelse
            </ul>

            <h2>Tujuan</h2>
            <ul class="check">
                @forelse ($school->goalsList() as $g)
                    <li>{{ $g }}</li>
                @empty
                    <li class="muted">Belum diisi.</li>
                @endforelse
            </ul>
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