@extends('layouts.site')

@section('title', 'Buat Akun — PPDB '.$school->name)

@section('content')
<section class="auth-section">
    <div class="wrap">
        <div class="auth-card">

            <aside class="auth-side">
                @if ($school->logoUrl())
                    <img class="auth-logo" src="{{ $school->logoUrl() }}" alt="Logo {{ $school->name }}">
                @endif
                <span class="kicker">Penerimaan Siswa Baru {{ $school->ppdb_year ?: '' }}</span>
                <h2>Daftarkan putra-putri Anda di {{ $school->name }}</h2>
                <p>Buat akun sekali, lalu isi formulir dan pantau hasilnya kapan saja.</p>

                <ol class="auth-steps">
                    <li>Buat akun orang tua / wali</li>
                    <li>Isi formulir dan unggah dokumen</li>
                    <li>Pantau status pendaftaran secara online</li>
                </ol>
            </aside>

            <div class="auth-main">
                <h1>Buat Akun</h1>
                <p class="auth-sub">Akun ini dipakai orang tua / wali untuk mendaftarkan calon siswa.</p>

                @if ($errors->any())
                    <div class="auth-alert">Ada isian yang belum benar. Silakan periksa kolom yang ditandai.</div>
                @endif

                <form method="post" action="{{ route('ppdb.register.submit', $school) }}" novalidate>
                    @csrf

                    <div class="auth-field">
                        <label for="name">Nama lengkap orang tua / wali</label>
                        <div class="auth-input">
                            <input id="name" type="text" name="name" value="{{ old('name') }}"
                                   placeholder="Nama sesuai KTP" required maxlength="150" autofocus autocomplete="name">
                        </div>
                        @error('name')<span class="err">{{ $message }}</span>@enderror
                    </div>

                    <div class="auth-field">
                        <label for="email">Email</label>
                        <div class="auth-input">
                            <input id="email" type="email" name="email" value="{{ old('email') }}"
                                   placeholder="nama@email.com" required maxlength="150" autocomplete="username">
                        </div>
                        @error('email')<span class="err">{{ $message }}</span>@enderror
                    </div>

                    <div class="auth-field">
                        <label for="password">Kata sandi</label>
                        <div class="auth-input has-toggle">
                            <input id="password" type="password" name="password"
                                   placeholder="Minimal 8 karakter" required minlength="8" autocomplete="new-password">
                            <button type="button" class="toggle-pw" data-toggle-password aria-label="Tampilkan kata sandi">Lihat</button>
                        </div>
                        @error('password')<span class="err">{{ $message }}</span>@enderror
                    </div>

                    <div class="auth-field">
                        <label for="password_confirmation">Ulangi kata sandi</label>
                        <div class="auth-input has-toggle">
                            <input id="password_confirmation" type="password" name="password_confirmation"
                                   placeholder="Ketik ulang kata sandi" required minlength="8" autocomplete="new-password">
                            <button type="button" class="toggle-pw" data-toggle-password aria-label="Tampilkan kata sandi">Lihat</button>
                        </div>
                    </div>

                    <button class="btn auth-submit" type="submit" style="margin-top:1.4rem">Buat akun &amp; lanjut mendaftar</button>
                    <p class="auth-note">Data Anda hanya dipakai untuk proses penerimaan siswa baru {{ $school->name }}.</p>
                </form>

                <p class="auth-switch">Sudah punya akun? <a href="{{ route('ppdb.login', $school) }}">Masuk</a></p>
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