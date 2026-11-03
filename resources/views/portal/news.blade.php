@extends('layouts.portal')

@section('title', 'Berita dan Informasi — '.$foundation->name)

@section('content')
<section class="page-title">
    <div class="wrap"><h1>Berita dan Informasi</h1></div>
</section>

<section class="section">
    <div class="wrap">
        <div class="filters">
            <a href="{{ route('portal.news') }}" @class(['active' => $source === ''])>Semua</a>
            <a href="{{ route('portal.news', ['sumber' => $foundation->slug]) }}" @class(['active' => $source === $foundation->slug])>Yayasan</a>
            @foreach ($jenjang->where('is_active', true) as $s)
                <a href="{{ route('portal.news', ['sumber' => $s->slug]) }}" @class(['active' => $source === $s->slug])>{{ $s->levelLabel() }}</a>
            @endforeach
        </div>

        @if ($posts->isEmpty())
            <p class="muted">Belum ada tulisan.</p>
        @else
            <div class="cards cols-3">
                @foreach ($posts as $post)
                    @include('portal._post-card', ['post' => $post])
                @endforeach
            </div>
            {{ $posts->links('partials.pager') }}
        @endif
    </div>
</section>
@endsection