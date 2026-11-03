@extends('layouts.site')

@section('title', 'Kontak — '.$school->name)

@section('content')
<section class="page-title">
    <div class="wrap"><h1>Hubungi Kami</h1></div>
</section>

<section class="section">
    <div class="wrap two-col">
        <div>
            <h2>Informasi Kontak</h2>
            <ul class="plain contact-list">
                @if ($school->address)<li><strong>Alamat</strong><br>{{ $school->address }}</li>@endif
                @if ($school->phone)<li><strong>Telepon</strong><br>{{ $school->phone }}</li>@endif
                @if ($school->email)<li><strong>Email</strong><br>{{ $school->email }}</li>@endif
                @if ($school->whatsapp)
                    <li><strong>WhatsApp</strong><br>
                        <a href="https://wa.me/{{ $school->whatsapp }}" target="_blank" rel="noopener">{{ $school->whatsapp }}</a>
                    </li>
                @endif
            </ul>

            @if ($school->maps_embed_url)
                <iframe class="map" src="{{ $school->maps_embed_url }}" loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade" title="Lokasi {{ $school->name }}"></iframe>
            @endif
        </div>

        <div>
            <h2>Kirim Pesan</h2>

            @if (session('status'))
                <div class="alert ok">{{ session('status') }}</div>
            @endif

            <form method="post" action="{{ route('contact.store', $school) }}" class="form">
                @csrf
                <div class="hp" aria-hidden="true">
                    <label>Jangan diisi <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                </div>

                <label>Nama
                    <input type="text" name="name" value="{{ old('name') }}" required maxlength="150">
                    @error('name')<span class="err">{{ $message }}</span>@enderror
                </label>
                <label>Email
                    <input type="email" name="email" value="{{ old('email') }}" maxlength="150">
                    @error('email')<span class="err">{{ $message }}</span>@enderror
                </label>
                <label>Nomor HP
                    <input type="text" name="phone" value="{{ old('phone') }}" maxlength="30">
                    @error('phone')<span class="err">{{ $message }}</span>@enderror
                </label>
                <label>Pesan
                    <textarea name="message" rows="5" required maxlength="2000">{{ old('message') }}</textarea>
                    @error('message')<span class="err">{{ $message }}</span>@enderror
                </label>
                @error('website')<span class="err">{{ $message }}</span>@enderror

                <button class="btn" type="submit">Kirim</button>
            </form>
        </div>
    </div>
</section>
@endsection