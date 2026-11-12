@extends('layouts.admin')

@section('title', 'Pendaftar PPDB')

@section('content')
<div class="admin-page-header">
    <div class="admin-page-header-info">
        <div class="admin-page-icon" style="background: rgba(20, 184, 166, 0.16); color: #14b8a6;">
            🎓
        </div>
        <div>
            <div class="admin-page-title-row">
                <h1>Pendaftar PPDB</h1>
                <span class="badge-total">{{ $items->total() }} Calon Siswa</span>
            </div>
            <p class="admin-page-desc">Kelola dan seleksi calon peserta didik baru untuk unit sekolah ini</p>
        </div>
    </div>
    <a class="btn btn-add" href="{{ route('admin.ppdb.export', request()->query()) }}">
        <span>⬇ Unduh CSV</span>
    </a>
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

<div class="table-wrapper-card">
    <div class="table-responsive">
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
                    <th class="right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $r)
                    <tr>
                        <td><span class="pill-badge pill-neutral">{{ $r->reg_number }}</span></td>
                        <td><strong class="cell-title">{{ $r->student_name }}</strong></td>
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
                        <td class="right actions-cell">
                            <a class="btn-action-edit" href="{{ route('admin.ppdb.show', $r->id) }}">Detail →</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="muted" style="text-align: center; padding: 2.5rem;">Belum ada pendaftar.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if ($items->hasPages())
    <div style="margin-top: 1.2rem;">
        {{ $items->links('partials.pager') }}
    </div>
@endif
@endsection
