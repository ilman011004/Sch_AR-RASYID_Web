@extends('layouts.admin')

@section('title', 'Pesan Masuk')

@section('content')
<style>
    .ms-head { display: flex; flex-wrap: wrap; align-items: baseline; justify-content: space-between; gap: .6rem; margin-bottom: 1.4rem; }
    .ms-head h1 { margin: 0; }
    .ms-head p { margin: 0; color: #667085; }

    .ms-alert { margin-bottom: 1rem; padding: .8rem 1.1rem; border-radius: 10px; background: #ecfdf3; border: 1px solid #abefc6; color: #067647; }

    .ms-list { display: grid; gap: 1rem; }
    .ms-card { padding: 1.2rem 1.4rem; border: 1px solid #e4e7ec; border-radius: 14px; background: #fff; }
    .ms-card.is-new { border-left: 5px solid #f5b800; }

    .ms-top { display: flex; flex-wrap: wrap; align-items: flex-start; justify-content: space-between; gap: .8rem; }
    .ms-name { font-size: 1.05rem; font-weight: 700; color: #101828; }
    .ms-meta { margin-top: .25rem; display: flex; flex-wrap: wrap; gap: .3rem 1rem; font-size: .86rem; color: #667085; }
    .ms-meta a { color: inherit; }

    .ms-badge { display: inline-block; padding: .2rem .65rem; border-radius: 999px; font-size: .72rem; font-weight: 700; letter-spacing: .04em; vertical-align: middle; margin-left: .4rem; }
    .ms-badge.new { background: #fef0c7; color: #93370d; }
    .ms-badge.done { background: #d1fadf; color: #05603a; }

    .ms-body { margin: .9rem 0 0; padding: .9rem 1.1rem; border-radius: 10px; background: #f9fafb; line-height: 1.7; overflow-wrap: anywhere; }

    .ms-actions { display: flex; flex-wrap: wrap; gap: .6rem; margin-top: 1rem; }
    .ms-actions form { margin: 0; }
    .ms-actions button { cursor: pointer; padding: .45rem .95rem; border-radius: 8px; font: inherit; font-size: .86rem; font-weight: 600; border: 1px solid #d0d5dd; background: #fff; color: #344054; }
    .ms-actions button:hover { background: #f2f4f7; }
    .ms-actions .danger { color: #b42318; border-color: #fda29b; }
    .ms-actions .danger:hover { background: #fef3f2; }

    .ms-reply-form { margin-top: 1rem; }
    .ms-reply-form textarea { width: 100%; padding: .7rem .9rem; border: 1px solid #d0d5dd; border-radius: 10px; font: inherit; }
    .ms-reply-form .ms-err { margin-top: .3rem; font-size: .88rem; color: #b42318; }
    .ms-reply-form button { margin-top: .6rem; cursor: pointer; padding: .55rem 1.2rem; border: 0; border-radius: 8px; font: inherit; font-weight: 700; background: #24485c; color: #fff; }
    .ms-reply-form button:hover { background: #1a3646; }

    .ms-empty { padding: 2.5rem 1rem; text-align: center; color: #667085; border: 1px dashed #d0d5dd; border-radius: 14px; }
</style>

<div class="ms-head">
    <h1>Pesan Masuk</h1>
    <p>{{ $messages->total() }} pesan</p>
</div>

@if (session('status'))
    <div class="ms-alert">{{ session('status') }}</div>
@endif

@if ($messages->isEmpty())
    <div class="ms-empty">Belum ada pesan masuk.</div>
@else
    <div class="ms-list">
        @foreach ($messages as $m)
            <article class="ms-card {{ $m->is_read ? '' : 'is-new' }}">
                <div class="ms-top">
                    <div>
                        <div class="ms-name">
                            {{ $m->name }}
                            @unless ($m->is_read)<span class="ms-badge new">BARU</span>@endunless
                            @if ($m->replied_at)<span class="ms-badge done">SUDAH DIBALAS</span>@endif
                        </div>
                        <div class="ms-meta">
                            @if ($m->email)<span>✉ <a href="mailto:{{ $m->email }}">{{ $m->email }}</a></span>@endif
                            @if ($m->phone)<span>📞 {{ $m->phone }}</span>@endif
                            <span>🕒 {{ $m->created_at->translatedFormat('d M Y, H:i') }}</span>
                        </div>
                    </div>
                </div>

                <div class="ms-body">{!! nl2br(e($m->message)) !!}</div>

                {{-- Formulir balas / jawaban yang sudah terkirim --}}
                @include('admin._message-reply', ['m' => $m])

                <div class="ms-actions">
                    @unless ($m->is_read)
                        <form method="post" action="{{ route('admin.messages.read', $m->id) }}">
                            @csrf
                            <button type="submit">Tandai sudah dibaca</button>
                        </form>
                    @endunless

                    <form method="post" action="{{ route('admin.messages.destroy', $m->id) }}"
                          onsubmit="return confirm('Hapus pesan dari {{ e($m->name) }}?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="danger">Hapus</button>
                    </form>
                </div>
            </article>
        @endforeach
    </div>

    <div style="margin-top:1.4rem">{{ $messages->links() }}</div>
@endif
@endsection