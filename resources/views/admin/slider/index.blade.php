@extends('layouts.site')

@section('title', 'Kelola Foto Slider')

@section('content')
<div class="page-title">
    <div class="wrap">
        <p class="crumb">Admin · {{ $school->name }}</p>
        <h1>Foto Slider Beranda</h1>
    </div>
</div>

<section class="section">
    <div class="wrap narrow">

        @if (session('ok'))
            <div class="alert ok">{{ session('ok') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert err-box">
                @foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach
            </div>
        @endif

        {{-- Foto yang sudah ada --}}
        <div class="slider-admin">
            @forelse ($photos as $p)
                <div class="card slider-item">
                    <img src="{{ $p->url() }}" alt="{{ $p->caption }}">
                    <form class="form" method="POST" action="{{ route('admin.slider.update', [$school, $p]) }}" enctype="multipart/form-data">
                        @csrf @method('PUT')
                        <label>Keterangan
                            <input type="text" name="caption" value="{{ old('caption', $p->caption) }}" maxlength="120">
                        </label>
                        <div class="row">
                            <label>Urutan
                                <input type="text" name="sort_order" value="{{ $p->sort_order }}" inputmode="numeric">
                            </label>
                            <label>Ganti foto
                                <input type="file" name="image" accept="image/*">
                            </label>
                        </div>
                        <label style="font-weight:500">
                            <input type="checkbox" name="is_active" value="1" @checked($p->is_active)> Tampilkan di slider
                        </label>
                        <div class="btns" style="margin-top:0">
                            <button class="btn btn-sm" type="submit">Simpan</button>
                    </form>
                    <form method="POST" action="{{ route('admin.slider.destroy', [$school, $p]) }}" onsubmit="return confirm('Hapus foto ini?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline" type="submit">Hapus</button>
                    </form>
                        </div>
                </div>
            @empty
                <p class="muted">Belum ada foto slider.</p>
            @endforelse
        </div>

        {{-- Tambah foto --}}
        @if ($photos->count() < $max)
            <h2 style="margin-top:2.5rem">Tambah Foto ({{ $photos->count() }}/{{ $max }})</h2>
            <form class="form" method="POST" action="{{ route('admin.slider.store', $school) }}" enctype="multipart/form-data">
                @csrf
                <label>Foto (JPG/PNG/WEBP, maks. 3 MB, disarankan rasio 16:9)
                    <input type="file" name="image" accept="image/*" required>
                </label>
                <label>Keterangan (opsional)
                    <input type="text" name="caption" maxlength="120">
                </label>
                <button class="btn" type="submit">Unggah</button>
            </form>
        @else
            <p class="muted" style="margin-top:2rem">Batas {{ $max }} foto sudah tercapai. Hapus salah satu untuk menambah yang baru.</p>
        @endif
    </div>
</section>
@endsection