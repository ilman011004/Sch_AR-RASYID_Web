@extends('layouts.site')

@section('title', 'Kontak — '.$school->name)

@section('content')
<section class="page-title">
    <div class="wrap">
        <p class="crumb"><a href="{{ route('home', $school) }}">Beranda</a> / Kontak</p>
        <h1>Hubungi Kami</h1>
    </div>
</section>

<style>
    .ct-login { text-align: center; padding: 2.2rem 1.5rem; border: 1px solid var(--line); border-radius: 16px; background: #fff; }
    .ct-login h3 { margin: 0 0 .5rem; color: var(--ink); }
    .ct-login p { margin: 0 auto 1.4rem; max-width: 46ch; color: var(--muted); }
    .ct-login .btns { display: flex; flex-wrap: wrap; justify-content: center; gap: .7rem; }

    .ct-as { margin: 0 0 1.3rem; padding: .95rem 1.2rem; border-radius: 12px; background: color-mix(in srgb, var(--ink) 6%, #fff); font-size: .95rem; line-height: 1.6; }
    .ct-count { display: block; margin-top: .3rem; font-size: .8rem; text-align: right; color: var(--muted); }
</style>

<section class="section">
    <div class="wrap narrow">
        <h2>Kirim Pesan</h2>

        @if (session('status'))
            <div class="alert ok">{{ session('status') }}</div>
        @endif

        @auth
            <p class="ct-as">
                Masuk sebagai <strong>{{ auth()->user()->name }}</strong>
                @if (auth()->user()->email)
                    ({{ auth()->user()->email }}).
                @endif
                Pesan ini hanya diterima oleh <strong>{{ $school->name }}</strong>.
                @if (auth()->user()->email)
                    Balasan akan dikirim ke email Anda.
                @endif
            </p>

            <form method="post" action="{{ route('contact.store', $school) }}" class="form">
                @csrf

                <label>Nama
                    <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" required maxlength="150">
                    @error('name')
                        <span class="err">{{ $message }}</span>
                    @enderror
                </label>

                <label>Pesan
                    <textarea name="message" rows="6" required maxlength="2000" data-ct-message>{{ old('message') }}</textarea>
                    <span class="ct-count" data-ct-count>0 / 2000</span>
                    @error('message')
                        <span class="err">{{ $message }}</span>
                    @enderror
                </label>

                <button class="btn" type="submit">Kirim Pesan</button>
            </form>
        @else
            <div class="ct-login">
                <h3>Masuk untuk mengirim pesan</h3>
                <p>Agar pesan Anda dapat kami jawab, silakan masuk ke akun Anda terlebih dahulu. Setelah masuk, Anda akan kembali ke halaman ini.</p>
                <div class="btns">
                    <a class="btn" href="{{ route('ppdb.login', $school) }}">Masuk</a>
                    <a class="btn btn-outline" href="{{ route('ppdb.register', $school) }}">Buat Akun</a>
                </div>
            </div>
        @endauth
    </div>
</section>

@include('partials.location', ['entity' => $school, 'contactUrl' => route('contact', $school)])

<script>
    (function () {
        var box = document.querySelector('[data-ct-message]');
        var out = document.querySelector('[data-ct-count]');
        if (!box || !out) return;

        var update = function () { out.textContent = box.value.length + ' / ' + (box.maxLength || 2000); };
        box.addEventListener('input', update);
        update();
    })();
</script>
@endsection