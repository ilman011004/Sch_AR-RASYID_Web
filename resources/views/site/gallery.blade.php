@extends('layouts.site')

@section('title', 'Galeri — '.$school->name)

@section('content')
<section class="page-title">
    <div class="wrap"><h1>Galeri Kegiatan</h1></div>
</section>

<section class="section">
    <div class="wrap">
        <div class="filters">
            <a href="{{ route('gallery', $school) }}" @class(['active' => ! $category])>Semua</a>
            @foreach (['pembelajaran' => 'Pembelajaran', 'acara' => 'Acara sekolah', 'prestasi' => 'Perlombaan & prestasi'] as $key => $label)
                <a href="{{ route('gallery', ['school' => $school, 'kategori' => $key]) }}" @class(['active' => $category === $key])>{{ $label }}</a>
            @endforeach
        </div>

        @if ($photos->isEmpty())
            <p class="muted">Belum ada foto.</p>
        @else
            <div class="photo-grid">
                @foreach ($photos as $p)
                    <figure>
                        <a href="{{ asset('storage/'.$p->image) }}" target="_blank" rel="noopener">
                            <img src="{{ asset('storage/'.$p->image) }}" alt="{{ $p->title }}" loading="lazy">
                        </a>
                        <figcaption>
                            {{ $p->title }}
                            @if ($p->school && $p->school->isFoundation())
                                <small style="display:inline-block; font-weight:700; color:var(--accent, #22a06b);">[Yayasan]</small>
                            @endif
                            @if ($p->caption)<small>{{ $p->caption }}</small>@endif
                        </figcaption>
                    </figure>
                @endforeach
            </div>
            {{ $photos->links('partials.pager') }}
        @endif
    </div>
</section>
@endsection