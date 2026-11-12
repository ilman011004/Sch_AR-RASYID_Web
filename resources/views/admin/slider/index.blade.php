@extends('layouts.site')

@section('title', 'Kelola Slider')

@section('content')
<div class="page-title">
    <div class="wrap">
        <p class="crumb">Admin · {{ $school->name }}</p>
        <h1>Slider Beranda (Foto / Video YouTube)</h1>
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

        {{-- Item yang sudah ada --}}
        <div class="slider-admin">
            @forelse ($photos as $p)
                <div class="card slider-item">
                    @if ($p->previewUrl())
                        <img src="{{ $p->previewUrl() }}" alt="{{ $p->caption }}">
                    @endif
                    @if ($p->youtubeId())
                        <p class="muted" style="margin:.4rem 0 0">▶ Video YouTube</p>
                    @endif

                    <form class="form" method="POST" action="{{ route('admin.slider.update', [$school, $p]) }}" enctype="multipart/form-data">
                        @csrf @method('PUT')
                        <label>Keterangan
                            <input type="text" name="caption" value="{{ old('caption', $p->caption) }}" maxlength="120">
                        </label>
                        <label>Link YouTube (opsional, kosongkan jika hanya foto)
                            <input type="url" name="video_url" value="{{ old('video_url', $p->video_url) }}" placeholder="https://www.youtube.com/watch?v=...">
                        </label>
                        <div class="row">
                            <label>Urutan
                                <input type="text" name="sort_order" value="{{ $p->sort_order }}" inputmode="numeric">
                            </label>
                            <label>{{ $p->image ? 'Ganti foto' : 'Foto (opsional)' }}
                                <input type="file" name="image" accept="image/*">
                            </label>
                        </div>
                        <label style="font-weight:500">
                            <input type="checkbox" name="is_active" value="1" @checked($p->is_active)> Tampilkan di slider
                        </label>
                        <div class="btns" style="margin-top:0">
                            <button class="btn btn-sm" type="submit">Simpan</button>
                        </div>
                    </form>

                    <form method="POST" action="{{ route('admin.slider.destroy', [$school, $p]) }}" onsubmit="return confirm('Hapus item ini?')" style="margin-top:.5rem">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline" type="submit">Hapus</button>
                    </form>
                </div>
            @empty
                <p class="muted">Belum ada slider.</p>
            @endforelse
        </div>

        {{-- Tambah --}}
        @if ($photos->count() < $max)
            <h2 style="margin-top:2.5rem">Tambah Slider ({{ $photos->count() }}/{{ $max }})</h2>
            <form class="form" method="POST" action="{{ route('admin.slider.store', $school) }}" enctype="multipart/form-data">
                @csrf
                <label>Foto (JPG/PNG/WEBP, maks. 3 MB, disarankan rasio 16:9)
                    <input type="file" name="image" accept="image/*">
                </label>
                <label>atau Link YouTube
                    <input type="url" name="video_url" value="{{ old('video_url') }}" placeholder="https://www.youtube.com/watch?v=...">
                </label>
                <small class="muted">Isi salah satu. Jika dua-duanya diisi, yang tampil adalah video (foto jadi cadangan).</small>
                <label>Keterangan (opsional)
                    <input type="text" name="caption" maxlength="120" value="{{ old('caption') }}">
                </label>
                <button class="btn" type="submit">Simpan</button>
            </form>
        @else
            <p class="muted" style="margin-top:2rem">Batas {{ $max }} item sudah tercapai. Hapus salah satu untuk menambah yang baru.</p>
        @endif
    </div>
</section>
@endsection