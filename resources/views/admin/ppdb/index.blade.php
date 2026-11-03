@extends('layouts.admin')

@section('title', 'Pendaftar PPDB')

@section('content')
<div class="page-head">
    <h1>Pendaftar PPDB</h1>
    <a class="btn" href="{{ route('admin.ppdb.export', array_filter(['status' => $filter, 'q' => $q])) }}">Unduh Excel (CSV)</a>
</div>

<form method="get" class="filter-bar">
    <input type="search" name="q" value="{{ $q }}" placeholder="Cari nama / nomor / orang tua">
    <select name="status" onchange="this.form.submit()">
        <option value="">Semua status</option>
        @foreach ($statuses as $key => $label)
            <option value="{{ $key }}" @selected($filter === $key)>{{ $label }}</option>
        @endforeach
    </select>
    <button class="btn btn-sm" type="submit">Cari</button>
</form>

<table class="table">
    <thead><tr><th>No. Pendaftaran</th><th>Nama calon siswa</th><th>Orang tua</th><th>No. HP</th><th>Tanggal</th><th>Status</th><th class="right">Aksi</th></tr></thead>
    <tbody>
        @forelse ($items as $r)
            <tr>
                <td>{{ $r->reg_number }}</td>
                <td>{{ $r->student_name }}</td>
                <td>{{ $r->parent_name }}</td>
                <td>{{ $r->parent_phone }}</td>
                <td>{{ $r->created_at->format('d/m/Y') }}</td>
                <td><span class="badge s-{{ $r->status }}">{{ $r->statusLabel() }}</span></td>
                <td class="right actions"><a href="{{ route('admin.ppdb.show', $r->id) }}">Buka</a></td>
            </tr>
        @empty
            <tr><td colspan="7" class="muted">Belum ada pendaftar.</td></tr>
        @endforelse
    </tbody>
</table>

{{ $items->links('partials.pager') }}
@endsection