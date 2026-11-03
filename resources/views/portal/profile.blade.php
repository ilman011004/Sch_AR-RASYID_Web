@extends('layouts.portal')

@section('title', $title.' — '.$foundation->name)

@section('content')
<section class="page-title">
    <div class="wrap"><h1>{{ $title }}</h1></div>
</section>

@if ($page === 'struktur')
    <section class="section">
        <div class="wrap">
            <div class="org">
                <div class="org-top">
                    @include('site._person', [
                        'name' => $foundation->head_name,
                        'role' => $foundation->head_title,
                        'photo' => $foundation->head_photo,
                    ])
                </div>

                @if ($staff->isNotEmpty())
                    <div class="people">
                        @foreach ($staff as $s)
                            @include('site._person', ['name' => $s->name, 'role' => $s->position, 'photo' => $s->photo])
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </section>

@elseif ($page === 'visi-misi')
    <section class="section">
        <div class="wrap narrow">
            <h2>Visi</h2>
            <p class="lead">{{ $foundation->vision ?: '—' }}</p>

            <h2>Misi</h2>
            <ul class="check">
                @forelse ($foundation->missionList() as $m)
                    <li>{{ $m }}</li>
                @empty
                    <li class="muted">Belum diisi.</li>
                @endforelse
            </ul>

            @if ($foundation->goalsList())
                <h2>Tujuan</h2>
                <ul class="check">
                    @foreach ($foundation->goalsList() as $g)<li>{{ $g }}</li>@endforeach
                </ul>
            @endif
        </div>
    </section>

@else
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
@endif
@endsection