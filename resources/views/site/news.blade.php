@extends('layouts.site')

@section('title', 'Berita — '.$school->name)

@section('content')
<section class="page-title">
    <div class="wrap"><h1>Berita dan Pengumuman</h1></div>
</section>

<section class="section">
    <div class="wrap">
        <div class="filters">
            <a href="{{ route('news', $school) }}" @class(['active' => ! $type])>Semua</a>
            <a href="{{ route('news', ['school' => $school, 'jenis' => 'berita']) }}" @class(['active' => $type === 'berita'])>Berita</a>
            <a href="{{ route('news', ['school' => $school, 'jenis' => 'pengumuman']) }}" @class(['active' => $type === 'pengumuman'])>Pengumuman</a>
        </div>

        @if ($posts->isEmpty())
            <p class="muted">Belum ada tulisan.</p>
        @else
            <div class="cards cols-3">
                @foreach ($posts as $post)
                    @include('site._post-card', ['post' => $post])
                @endforeach
            </div>
            {{ $posts->links('partials.pager') }}
        @endif
    </div>
</section>
@endsection