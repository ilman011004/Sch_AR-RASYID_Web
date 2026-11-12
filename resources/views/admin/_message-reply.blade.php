@php
    // Input lama dan error hanya ditampilkan pada pesan yang baru saja dibalas
    $mine = (int) old('reply_id') === (int) $m->id;
@endphp

@if ($m->replied_at)
    <div style="margin-top:.9rem;padding:.9rem 1.1rem;border-left:4px solid #2d8f6b;background:#f0faf5;border-radius:8px">
        <strong>Sudah dibalas</strong>
        <small style="color:#667085">
            · {{ $m->replied_at->translatedFormat('d M Y H:i') }}
            @if ($m->repliedBy) oleh {{ $m->repliedBy->name }} @endif
        </small>
        <div style="margin-top:.4rem;line-height:1.7">{!! nl2br(e($m->reply)) !!}</div>
    </div>
@elseif ($m->email)
    <form class="ms-reply-form" method="post" action="{{ route('admin.messages.reply', $m->id) }}">
        @csrf
        <input type="hidden" name="reply_id" value="{{ $m->id }}">
        <textarea name="reply" rows="3" required maxlength="5000"
                  placeholder="Tulis balasan. Jawaban akan dikirim ke {{ $m->email }}">{{ $mine ? old('reply') : '' }}</textarea>
        @if ($mine)
            @error('reply')<div class="ms-err">{{ $message }}</div>@enderror
        @endif
        <button type="submit">Kirim Balasan</button>
    </form>
@else
    <p style="margin:.9rem 0 0;color:#667085;font-size:.9rem">Pengirim tidak mencantumkan email, balasan tidak dapat dikirim.</p>
@endif