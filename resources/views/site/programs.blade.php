@extends('layouts.site')

@section('title', $title.' — '.$school->name)

@section('content')
<section class="page-title">
    <div class="wrap">
        <p class="crumb"><a href="{{ route('home', $school) }}">Beranda</a> / Program Akademik</p>
        <h1>{{ $title }}</h1>
    </div>
</section>

@if ($page === 'kurikulum')
    <section class="section">
        <div class="wrap narrow">
            @if ($school->curriculum)
                <div class="prose">{!! nl2br(e($school->curriculum)) !!}</div>
            @else
                <p class="muted">Belum diisi.</p>
            @endif
        </div>
    </section>
@else
    <section class="section">
        <div class="wrap">
            @if ($items->isEmpty())
                <p class="muted">Belum ada data.</p>
            @else
                <div class="cards cols-3">
                    @foreach ($items as $p)
                        <article class="card">
                            @if ($p->image)
                                <img class="cover" src="{{ asset('storage/'.$p->image) }}" alt="{{ $p->title }}" loading="lazy">
                            @endif
                            <div class="body">
                                <h3>{{ $p->title }}</h3>
                                <p>{!! nl2br(e($p->description)) !!}</p>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endif
@endsection