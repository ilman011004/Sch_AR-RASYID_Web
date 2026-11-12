<h2>Pesan baru dari situs {{ $school->name }}</h2>

<p>
    <strong>Nama:</strong> {{ $contact->name }}<br>
    @if ($contact->email)<strong>Email:</strong> {{ $contact->email }}<br>@endif
    @if ($contact->phone)<strong>No. HP:</strong> {{ $contact->phone }}<br>@endif
    <strong>Waktu:</strong> {{ $contact->created_at->translatedFormat('l, d F Y H:i') }}
</p>

<hr>
<p>{!! nl2br(e($contact->message)) !!}</p>
<hr>

<p style="color:#667085;font-size:13px">
    @if ($contact->email)
        Balas email ini untuk menjawab langsung ke pengirim.
    @else
        Pengirim tidak memiliki email. Jawab melalui panel admin.
    @endif
</p>