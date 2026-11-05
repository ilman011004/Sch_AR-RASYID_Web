@extends('layouts.admin')

@section('title', 'Pendaftar PPDB')

@section('content')
<div class="page-head">
    <h1>Pendaftar PPDB</h1>
    <a class="btn btn-sm" href="{{ route('admin.ppdb.export', request()->query()) }}">Unduh CSV</a>
</div>

<form method="get" class="filter-bar">
    <input type="text" name="q" value="{{ $q }}" placeholder="Cari nama / nomor / orang tua">
    <select name="stage">
        <option value="">Semua tahap</option>
        @foreach ($stages as $n => $label)
            <option value="{{ $n }}" @selected((string) $stageFilter === (string) $n)>Tahap {{ $n }} — {{ $label }}</option>
        @endforeach
    </select>
    <select name="status">
        <option value="">Semua status</option>
        @foreach ($statuses as $key => $label)
            <option value="{{ $key }}" @selected($filter === $key)>{{ $label }}</option>
        @endforeach
    </select>
    <button class="btn btn-sm" type="submit">Terapkan</button>
</form>

<table class="table">
    <thead>
        <tr>
            <th>No. Pendaftaran</th>
            <th>Calon siswa</th>
            <th>Orang tua / wali</th>
            <th>Tahap</th>
            <th>Berkas</th>
            <th>Status</th>
            <th>Daftar</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @forelse ($items as $r)
            <tr>
                <td>{{ $r->reg_number }}</td>
                <td>{{ $r->student_name }}</td>
                <td>{{ $r->parent_name }}<br><small>{{ $r->parent_phone }}</small></td>
                <td><span class="badge stage-{{ $r->stage }}">{{ $r->stage }}. {{ $r->stageLabel() }}</span></td>
                <td>
                    @if ($r->docs_verified)
                        <span class="badge s-diterima">Lengkap</span>
                    @elseif ($r->docsComplete())
                        <span class="badge s-diproses">Perlu dicek</span>
                    @else
                        <span class="badge s-ditolak">Kurang</span>
                    @endif
                </td>
                <td><span class="badge s-{{ $r->status }}">{{ $r->statusLabel() }}</span></td>
                <td>{{ $r->created_at->format('d/m/Y') }}</td>
                <td class="actions"><a href="{{ route('admin.ppdb.show', $r->id) }}">Detail</a></td>
            </tr>
        @empty
            <tr><td colspan="8" class="muted">Belum ada pendaftar.</td></tr>
        @endforelse
    </tbody>
</table>

@if ($items->hasPages())
    <div class="pager">
        @if ($items->onFirstPage())
            <span class="disabled">← Sebelumnya</span>
        @else
            <a href="{{ $items->previousPageUrl() }}">← Sebelumnya</a>
        @endif

        <span>Halaman {{ $items->currentPage() }} dari {{ $items->lastPage() }}</span>

        @if ($items->hasMorePages())
            <a href="{{ $items->nextPageUrl() }}">Berikutnya →</a>
        @else
            <span class="disabled">Berikutnya →</span>
        @endif
    </div>
@endif
@endsection