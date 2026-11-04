@extends('layouts.site')

@section('title', 'Kontak — '.$school->name)

@section('content')
<section class="page-title">
    <div class="wrap">
        <p class="crumb"><a href="{{ route('home', $school) }}">Beranda</a> / Kontak</p>
        <h1>Hubungi Kami</h1>
    </div>
</section>

<section class="section">
    <div class="wrap narrow">
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
</section>

@include('partials.location', ['entity' => $school, 'contactUrl' => route('contact', $school)])
@endsection