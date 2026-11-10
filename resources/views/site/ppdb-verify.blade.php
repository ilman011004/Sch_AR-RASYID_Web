@extends('layouts.site')

@section('title', 'Verifikasi Email — PPDB '.$school->name)

@section('content')
<section class="auth-section">
    <div class="wrap">
        <div class="auth-card">

            <aside class="auth-side">
                @if ($school->logoUrl())
                    <img class="auth-logo" src="{{ $school->logoUrl() }}" alt="Logo {{ $school->name }}">
                @endif
                <span class="kicker">Penerimaan Siswa Baru {{ $school->ppdb_year ?: '' }}</span>
                <h2>Verifikasi email Anda</h2>
                <p>Satu langkah lagi. Masukkan kode yang kami kirim ke email untuk menyelesaikan pembuatan akun.</p>

                <ol class="auth-steps">
                    <li>Buat akun orang tua / wali</li>
                    <li>Isi formulir dan unggah dokumen</li>
                    <li>Pantau status pendaftaran secara online</li>
                </ol>
            </aside>

            <div class="auth-main">
                <h1>Masukkan Kode</h1>
                <p class="auth-sub">Kode 6 digit dikirim ke <strong>{{ $email }}</strong>. Berlaku 10 menit. Cek juga folder spam.</p>

                @if (session('status'))
                    <div class="auth-alert" style="color:#166534;background:#dcfce7;border-color:#bbf7d0">{{ session('status') }}</div>
                @endif

                @error('email')
                    <div class="auth-alert">{{ $message }}</div>
                @enderror

                <form method="post" action="{{ route('ppdb.verify.submit', $school) }}" novalidate>
                    @csrf

                    <div class="auth-field">
                        <label for="code">Kode verifikasi</label>
                        <div class="auth-input">
                            <input id="code" type="text" name="code" inputmode="numeric" maxlength="6"
                                   placeholder="123456" required autofocus autocomplete="one-time-code">
                        </div>
                        @error('code')<span class="err">{{ $message }}</span>@enderror
                    </div>

                    <button class="btn auth-submit" type="submit" style="margin-top:1.4rem">Verifikasi &amp; buat akun</button>
                </form>

                <form method="post" action="{{ route('ppdb.verify.resend', $school) }}" style="text-align:center;margin:1.25rem 0 0">
                    @csrf
                    <span class="auth-switch" style="margin:0">Belum menerima kode?
                        <button type="submit" style="background:none;border:0;padding:0;font:inherit;color:inherit;text-decoration:underline;cursor:pointer">Kirim ulang kode</button>
                    </span>
                </form>

                <p style="text-align:center;margin:1rem 0 0">
                    <a class="auth-back" href="{{ route('ppdb.register', $school) }}">← Salah email? Kembali ke pendaftaran akun</a>
                </p>
            </div>
        </div>
    </div>
</section>
@endsection