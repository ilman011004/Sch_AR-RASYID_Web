<p>Halo {{ $contact->name }},</p>

<p>Terima kasih telah menghubungi <strong>{{ $school->name }}</strong>. Berikut jawaban atas pesan Anda:</p>

<div style="padding:14px 18px;border-left:4px solid #f5b800;background:#fffaf0">
    {!! nl2br(e($contact->reply)) !!}
</div>

<p style="margin-top:22px;color:#667085;font-size:13px">Pesan Anda sebelumnya:</p>
<blockquote style="margin:0;padding-left:14px;border-left:3px solid #d0d5dd;color:#667085;font-size:14px">
    {!! nl2br(e($contact->message)) !!}
</blockquote>

<p style="margin-top:22px">Salam hormat,<br><strong>{{ $school->name }}</strong></p>