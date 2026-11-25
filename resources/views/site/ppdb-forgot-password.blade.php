@extends('layouts.site')

@section('title', 'Lupa Kata Sandi — PPDB '.$school->name)

@section('content')
<section class="auth-section">
    <div class="wrap">
        <div class="auth-card">

            <aside class="auth-side">
                @if ($school->logoUrl())
                    <img class="auth-logo" src="{{ $school->logoUrl() }}" alt="Logo {{ $school->name }}">
                @endif
                <span class="kicker">Bantuan Akun Pendaftar</span>
                <h2>{{ $school->name }}</h2>
                <p>Masukkan alamat email akun orang tua / wali yang Anda gunakan saat mendaftar untuk menerima kode reset kata sandi.</p>

                <ol class="auth-steps">
                    <li>Masukkan email akun Anda</li>
                    <li>Dapatkan 6 digit kode OTP lewat email</li>
                    <li>Buat kata sandi baru dan masuk kembali</li>
                </ol>
            </aside>

            <div class="auth-main">
                <h1>Lupa Kata Sandi</h1>
                <p class="auth-sub">Kami akan mengirimkan 6 digit kode verifikasi ke email Anda.</p>

                @if (session('status'))
                    <div class="auth-alert" style="color:#166534;background:#dcfce7;border-color:#bbf7d0">{{ session('status') }}</div>
                @endif

                @if (session('error'))
                    <div class="auth-alert">{{ session('error') }}</div>
                @endif
                @error('email')
                    <div class="auth-alert">{{ $message }}</div>
                @enderror

                <form method="post" action="{{ route('ppdb.password.email', $school) }}" novalidate>
                    @csrf

                    <div class="auth-field">
                        <label for="email">Alamat Email Terdaftar</label>
                        <div class="auth-input">
                            <input id="email" type="email" name="email" value="{{ old('email') }}"
                                   placeholder="nama@email.com" required autocomplete="email" autofocus>
                        </div>
                    </div>

                    <button class="btn auth-submit" type="submit">Kirim Kode Verifikasi</button>
                </form>

                <p class="auth-switch">Ingat kata sandi Anda? <a href="{{ route('ppdb.login', $school) }}">Masuk ke akun</a></p>
                <p style="text-align:center;margin:0">
                    <a class="auth-back" href="{{ route('ppdb', $school) }}">← Kembali ke informasi pendaftaran</a>
                </p>
            </div>
        </div>
    </div>
</section>
@endsection
