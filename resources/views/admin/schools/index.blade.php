@extends('layouts.admin')

@section('title', 'Daftar Sekolah')

@section('content')
<div class="page-head">
    <h1>Daftar Sekolah / Jenjang</h1>
    <a class="btn" href="{{ route('admin.schools.create') }}">+ Tambah</a>
</div>

<table class="table">
    <thead><tr><th>Nama</th><th>Jenjang</th><th>Alamat situs</th><th>Status</th><th class="right">Aksi</th></tr></thead>
    <tbody>
        @foreach ($schools as $s)
            <tr>
                <td>{{ $s->name }}</td>
                <td>{{ $s->levelLabel() }}</td>
                <td>
                    @if ($s->is_active)
                        <a href="{{ route('home', $s) }}" target="_blank" rel="noopener">{{ route('home', $s) }}</a>
                    @else
                        <span class="muted">{{ $s->slug }}</span>
                    @endif
                </td>
                <td>{{ $s->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                <td class="right actions"><a href="{{ route('admin.schools.edit', $s->slug) }}">Ubah</a></td>
            </tr>
        @endforeach
    </tbody>
</table>

<p class="muted small">Sekolah nonaktif tidak bisa dibuka oleh pengunjung, tetapi kontennya tetap bisa diisi dari panel ini.</p>
@endsection