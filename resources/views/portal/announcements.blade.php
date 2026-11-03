@extends('layouts.portal')

@section('title', 'Pengumuman — '.$foundation->name)

@section('content')
<section class="page-title">
    <div class="wrap"><h1>Pengumuman</h1></div>
</section>

<section class="section">
    <div class="wrap">
        @if ($posts->isEmpty())
            <p class="muted">Belum ada pengumuman.</p>
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