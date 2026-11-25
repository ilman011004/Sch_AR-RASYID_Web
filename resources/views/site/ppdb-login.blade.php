@extends('layouts.site')

@section('title', 'Masuk — PPDB '.$school->name)

@section('content')
<section class="auth-section">
    <div class="wrap">
        <div class="auth-card">

            <aside class="auth-side">
                @if ($school->logoUrl())
                    <img class="auth-logo" src="{{ $school->logoUrl() }}" alt="Logo {{ $school->name }}">
                @endif
                <span class="kicker">Penerimaan Siswa Baru {{ $school->ppdb_year ?: '' }}</span>
                <h2>{{ $school->name }}</h2>
                <p>Masuk ke akun Anda untuk mengisi formulir pendaftaran dan memantau status penerimaan.</p>

                <ol class="auth-steps">
                    <li>Masuk atau buat akun orang tua / wali</li>
                    <li>Isi formulir dan unggah dokumen</li>
                    <li>Pantau status pendaftaran secara online</li>
                </ol>
            </aside>

            <div class="auth-main">
                <h1>Masuk</h1>
                <p class="auth-sub">Gunakan akun orang tua / wali yang sudah terdaftar.</p>

                @if (session('status'))
                    <div class="auth-alert" style="color:#166534;background:#dcfce7;border-color:#bbf7d0">{{ session('status') }}</div>
                @endif

                @if (session('error'))
                    <div class="auth-alert">{{ session('error') }}</div>
                @endif
                @error('email')
                    <div class="auth-alert">{{ $message }}</div>
                @enderror

                <form method="post" action="{{ route('ppdb.login.submit', $school) }}" novalidate>
                    @csrf

                    <div class="auth-field">
                        <label for="email">Email</label>
                        <div class="auth-input">
                            <input id="email" type="email" name="email" value="{{ old('email') }}"
                                   placeholder="nama@email.com" required autocomplete="username"
                                   @if (! old('email')) autofocus @endif>
                        </div>
                    </div>

                    <div class="auth-field">
                        <label for="password">Kata sandi</label>
                        <div class="auth-input has-toggle">
                            <input id="password" type="password" name="password"
                                   placeholder="Masukkan kata sandi" required autocomplete="current-password"
                                   @if (old('email')) autofocus @endif>
                            <button type="button" class="toggle-pw" data-toggle-password aria-label="Tampilkan kata sandi">Lihat</button>
                        </div>
                        @error('password')<span class="err">{{ $message }}</span>@enderror
                    </div>

                    <div class="auth-row" style="display:flex;justify-content:space-between;align-items:center">
                        <label class="auth-check">
                            <input type="checkbox" name="remember" value="1" @checked(old('remember'))> Ingat saya
                        </label>
                        <a href="{{ route('ppdb.password.request', $school) }}" style="font-size:.85rem;color:var(--primary);text-decoration:none;font-weight:600">Lupa kata sandi?</a>
                    </div>

                    <button class="btn auth-submit" type="submit">Masuk</button>
                </form>

                <p class="auth-switch">Belum punya akun? <a href="{{ route('ppdb.register', $school) }}">Buat akun baru</a></p>
                <p style="text-align:center;margin:0">
                    <a class="auth-back" href="{{ route('ppdb', $school) }}">← Kembali ke informasi pendaftaran</a>
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