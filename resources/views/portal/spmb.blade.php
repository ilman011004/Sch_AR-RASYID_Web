@extends('layouts.portal')

@section('title', 'Info Pendaftaran — '.$foundation->name)

@section('content')
<section class="page-title">
    <div class="wrap"><h1>Info Pendaftaran Siswa Baru</h1></div>
</section>

<section class="section">
    <div class="wrap narrow">
        @forelse ($sections as $s)
            @if ($s['title'])<h2>{{ $s['title'] }}</h2>@endif
            @if ($s['items'])
                <ul class="check">
                    @foreach ($s['items'] as $item)<li>{{ $item }}</li>@endforeach
                </ul>
            @endif
        @empty
            <p class="muted">Informasi umum belum tersedia. Silakan pilih jenjang di bawah.</p>
        @endforelse
    </div>
</section>

<section class="section alt">
    <div class="wrap">
        <h2 class="center">Pendaftaran per Jenjang</h2>
        <div class="cards cols-4">
            @foreach ($jenjang as $s)
                <div class="card center portal-card" style="--primary: {{ $s->safeColor() }}">
                    <h3>{{ $s->name }}</h3>
                    @if (! $s->is_active)
                        <p class="muted">Segera hadir</p>
                    @elseif ($s->ppdbIsOpen())
                        <p><span class="tag tag-ok">Dibuka</span></p>
                        <a class="btn btn-sm" href="{{ route('ppdb', $s) }}">Daftar</a>
                    @else
                        <p><span class="tag tag-warn">Ditutup</span></p>
                        <a href="{{ route('ppdb', $s) }}">Lihat info</a>
                    @endif
                    @if ($s->is_active)
                        <p class="small" style="margin-top:.6rem"><a href="{{ route('ppdb.status', $s) }}">Cek status pendaftaran</a></p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</section>
@endsection