@extends('layouts.admin')

@section('title', 'Pesan Masuk')

@section('content')
<div class="page-head"><h1>Pesan Masuk</h1></div>

@forelse ($messages as $m)
    <article class="panel msg {{ $m->is_read ? '' : 'unread' }}">
        <div class="msg-head">
            <strong>{{ $m->name }}</strong>
            <span class="muted small">{{ $m->created_at->translatedFormat('d F Y H:i') }}</span>
        </div>
        <p class="muted small">
            {{ $m->email }} {{ $m->email && $m->phone ? '·' : '' }} {{ $m->phone }}
        </p>
        <p>{!! nl2br(e($m->message)) !!}</p>

        <div class="actions">
            @unless ($m->is_read)
                <form method="post" action="{{ route('admin.messages.read', $m->id) }}">
                    @csrf
                    <button type="submit" class="link-btn">Tandai sudah dibaca</button>
                </form>
            @endunless
            <form method="post" action="{{ route('admin.messages.destroy', $m->id) }}" onsubmit="return confirm('Hapus pesan ini?')">
                @csrf @method('DELETE')
                <button type="submit" class="link-btn danger">Hapus</button>
            </form>
        </div>
    </article>
@empty
    <p class="muted">Belum ada pesan.</p>
@endforelse

{{ $messages->links('partials.pager') }}
@endsection