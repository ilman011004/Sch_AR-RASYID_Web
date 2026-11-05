@extends('layouts.admin')

@section('title', 'Semua Pendaftar')

@section('content')
<div class="page-head">
    <h1>Semua Pendaftar <small>PAUD sampai SMA</small></h1>
    <a class="btn btn-sm" href="{{ route('admin.ppdb.all.export', request()->query()) }}">Unduh CSV semua jenjang</a>
</div>

{{-- Ringkasan per sekolah --}}
<table class="table" style="margin-bottom:1.4rem">
    <thead>
        <tr>
            <th>Sekolah</th>
            <th class="right">Total</th>
            <th class="right">Tahap 1</th>
            <th class="right">Tahap 2</th>
            <th class="right">Tahap 3</th>
            <th class="right">Diterima</th>
            <th class="right">Tidak diterima</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @php $sum = ['total' => 0, 's1' => 0, 's2' => 0, 's3' => 0, 'diterima' => 0, 'ditolak' => 0]; @endphp
        @foreach ($schools as $s)
            @php
                $row = $summary[$s->id];
                foreach ($sum as $k => $v) { $sum[$k] += $row[$k]; }
            @endphp
            <tr>
                <td>
                    <strong>{{ $s->name }}</strong>
                    <small>{{ $s->levelLabel() }}</small>
                    @unless ($s->ppdbIsOpen())<span class="badge">Ditutup</span>@endunless
                </td>
                <td class="right">{{ $row['total'] }}</td>
                <td class="right">{{ $row['s1'] }}</td>
                <td class="right">{{ $row['s2'] }}</td>
                <td class="right">{{ $row['s3'] }}</td>
                <td class="right">{{ $row['diterima'] }}</td>
                <td class="right">{{ $row['ditolak'] }}</td>
                <td class="actions">
                    <a href="{{ route('admin.ppdb.all', array_merge(request()->except('page'), ['school' => $s->id])) }}">Lihat</a>
                </td>
            </tr>
        @endforeach
        <tr>
            <td><strong>Seluruh jenjang</strong></td>
            <td class="right"><strong>{{ $sum['total'] }}</strong></td>
            <td class="right"><strong>{{ $sum['s1'] }}</strong></td>
            <td class="right"><strong>{{ $sum['s2'] }}</strong></td>
            <td class="right"><strong>{{ $sum['s3'] }}</strong></td>
            <td class="right"><strong>{{ $sum['diterima'] }}</strong></td>
            <td class="right"><strong>{{ $sum['ditolak'] }}</strong></td>
            <td></td>
        </tr>
    </tbody>
</table>

{{-- Filter --}}
<form method="get" class="filter-bar">
    <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="Cari nama / nomor / orang tua">
    <select name="school">
        <option value="">Semua sekolah</option>
        @foreach ($schools as $s)
            <option value="{{ $s->id }}" @selected($filters['school'] === $s->id)>{{ $s->name }}</option>
        @endforeach
    </select>
    <select name="year">
        <option value="">Semua tahun</option>
        @foreach ($years as $y)
            <option value="{{ $y }}" @selected($filters['year'] === (int) $y)>{{ $y }}</option>
        @endforeach
    </select>
    <select name="stage">
        <option value="">Semua tahap</option>
        @foreach ($stages as $n => $label)
            <option value="{{ $n }}" @selected((string) $filters['stage'] === (string) $n)>Tahap {{ $n }} — {{ $label }}</option>
        @endforeach
    </select>
    <select name="status">
        <option value="">Semua status</option>
        @foreach ($statuses as $key => $label)
            <option value="{{ $key }}" @selected($filters['status'] === $key)>{{ $label }}</option>
        @endforeach
    </select>
    <button class="btn btn-sm" type="submit">Terapkan</button>
    <a class="btn btn-sm" style="background:#6b7280" href="{{ route('admin.ppdb.all') }}">Reset</a>
</form>

<table class="table">
    <thead>
        <tr>
            <th>Sekolah</th>
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
                <td>{{ $r->school?->name ?? '-' }}</td>
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
            <tr><td colspan="9" class="muted">Belum ada pendaftar.</td></tr>
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