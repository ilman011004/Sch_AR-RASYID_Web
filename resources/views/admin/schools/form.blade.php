@extends('layouts.admin')

@section('title', $school ? 'Ubah Sekolah' : 'Tambah Sekolah')

@section('content')
<div class="page-head">
    <h1>{{ $school ? 'Ubah' : 'Tambah' }} Sekolah</h1>
    <a href="{{ route('admin.schools.index') }}">← Kembali</a>
</div>

<form method="post" class="panel"
      action="{{ $school ? route('admin.schools.update', $school->slug) : route('admin.schools.store') }}">
    @csrf
    @if ($school) @method('PUT') @endif

    <div class="field">
        <label for="name">Nama sekolah</label>
        <input id="name" name="name" value="{{ old('name', $school?->name) }}" required maxlength="150">
    </div>

    <div class="field">
        <label for="slug">Alamat singkat (slug)</label>
        <input id="slug" name="slug" value="{{ old('slug', $school?->slug) }}" required maxlength="30" pattern="[a-z0-9_-]+">
        <small>Huruf kecil, angka, dan strip. Contoh: <code>sd</code> → situs di <code>/sd</code> atau <code>sd.domain</code>. Mengubahnya akan mengubah alamat situs.</small>
    </div>

    <div class="field">
        <label for="level">Jenjang</label>
        <select id="level" name="level">
            @foreach (config('school.levels') as $key => $label)
                <option value="{{ $key }}" @selected(old('level', $school?->level) === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="field">
        <label for="sort">Urutan</label>
        <input id="sort" type="number" min="0" name="sort" value="{{ old('sort', $school?->sort ?? 0) }}">
    </div>

    <div class="field">
        <input type="hidden" name="is_active" value="0">
        <label class="check">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $school?->is_active ?? true))>
            Aktif (situs bisa dibuka pengunjung)
        </label>
    </div>

    <button type="submit" class="btn">Simpan</button>
</form>
@endsection