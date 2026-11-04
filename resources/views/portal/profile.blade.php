@extends('layouts.portal')

@section('title', $title.' — '.$foundation->name)

@section('content')
<section class="page-title">
    <div class="wrap">
        <p class="crumb"><a href="{{ route('portal') }}">Beranda</a> / Profil</p>
        <h1>{{ $title }}</h1>
    </div>
</section>

@if ($page === 'sejarah')
    <section class="section">
        <div class="wrap narrow">
            @if ($foundation->founded_year)
                <p class="muted">Berdiri sejak tahun {{ $foundation->founded_year }}</p>
            @endif
            @if ($foundation->history)
                <div class="prose">{!! nl2br(e($foundation->history)) !!}</div>
            @else
                <p class="muted">Belum diisi.</p>
            @endif
        </div>
    </section>

@elseif ($page === 'visi-misi')
    <section class="section">
        <div class="wrap">
            @include('partials.vm', ['entity' => $foundation, 'goals' => true])
        </div>
    </section>

@elseif ($page === 'struktur')
    <section class="section">
        <div class="wrap">
            @if ($foundation->structureImageUrl())
                <a href="{{ $foundation->structureImageUrl() }}" target="_blank" rel="noopener">
                    <img class="structure-img" src="{{ $foundation->structureImageUrl() }}" alt="Struktur organisasi {{ $foundation->name }}">
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
                    'name' => $foundation->head_name,
                    'role' => $foundation->head_title,
                    'photo' => $foundation->head_photo,
                ])
            </div>

            @if ($staff->isEmpty())
                <p class="muted center">Data pegawai belum diisi.</p>
            @else
                <div class="people">
                    @foreach ($staff as $s)
                        @include('site._person', [
                            'name' => $s->name,
                            'role' => trim($s->position.($s->subject ? ' · '.$s->subject : '')),
                            'photo' => $s->photo,
                        ])
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endif
@endsection