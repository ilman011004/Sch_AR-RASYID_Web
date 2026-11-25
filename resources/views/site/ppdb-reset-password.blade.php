@extends('layouts.site')

@section('title', 'Atur Kata Sandi Baru — PPDB '.$school->name)

@section('content')
<section class="auth-section">
    <div class="wrap">
        <div class="auth-card">

            <aside class="auth-side">
                @if ($school->logoUrl())
                    <img class="auth-logo" src="{{ $school->logoUrl() }}" alt="Logo {{ $school->name }}">
                @endif
                <span class="kicker">Reset Kata Sandi</span>
                <h2>{{ $school->name }}</h2>
                <p>Masukkan kode verifikasi yang kami kirimkan ke email Anda, lalu buat kata sandi baru.</p>

                <ol class="auth-steps">
                    <li>Cek kotak masuk / folder spam email</li>
                    <li>Ketik 6 digit kode OTP</li>
                    <li>Buat kata sandi baru (minimal 8 karakter)</li>
                </ol>
            </aside>

            <div class="auth-main">
                <h1>Kata Sandi Baru</h1>
                <p class="auth-sub">Kode verifikasi dikirimkan ke <strong>{{ $email }}</strong>.</p>

                @if (session('status'))
                    <div class="auth-alert" style="color:#166534;background:#dcfce7;border-color:#bbf7d0">{{ session('status') }}</div>
                @endif

                @if ($errors->any())
                    <div class="auth-alert">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="post" action="{{ route('ppdb.password.update', $school) }}" novalidate>
                    @csrf

                    <div class="auth-field">
                        <label for="code">Kode Verifikasi (6 Digit)</label>
                        <div class="auth-input">
                            <input id="code" type="text" name="code" value="{{ old('code') }}"
                                   inputmode="numeric" autocomplete="one-time-code" maxlength="6" pattern="[0-9]{6}"
                                   placeholder="Contoh: 123456" required autofocus
                                   oninput="this.value=this.value.replace(/\D/g,'')">
                        </div>
                        <small class="hint">Cek kotak masuk atau folder spam email Anda.</small>
                    </div>

                    <div class="auth-field">
                        <label for="password">Kata Sandi Baru</label>
                        <div class="auth-input has-toggle">
                            <input id="password" type="password" name="password"
                                   placeholder="Minimal 8 karakter" required autocomplete="new-password">
                            <button type="button" class="toggle-pw" data-toggle-password aria-label="Tampilkan kata sandi">Lihat</button>
                        </div>
                    </div>

                    <div class="auth-field">
                        <label for="password_confirmation">Ulangi Kata Sandi Baru</label>
                        <div class="auth-input has-toggle">
                            <input id="password_confirmation" type="password" name="password_confirmation"
                                   placeholder="Ketik ulang kata sandi baru" required autocomplete="new-password">
                            <button type="button" class="toggle-pw" data-toggle-password aria-label="Tampilkan kata sandi">Lihat</button>
                        </div>
                    </div>

                    <button class="btn auth-submit" type="submit">Simpan Kata Sandi Baru</button>
                </form>

                <p class="auth-switch">Belum menerima kode? <a href="{{ route('ppdb.password.request', $school) }}">Kirim ulang kode</a></p>
                <p style="text-align:center;margin:0">
                    <a class="auth-back" href="{{ route('ppdb.login', $school) }}">← Batal dan kembali ke halaman masuk</a>
                </p>
            </div>
        </div>
    </div>
</section>

<script>
    document.querySelectorAll('[data-toggle-password]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = btn.parentElement.querySelector('input');
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.textContent = show ? 'Sembunyi' : 'Lihat';
            btn.setAttribute('aria-label', show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
        });
    });
</script>
@endsection
