@extends('layouts.admin')

@section('title', 'Daftar Sekolah')

@section('content')
<div class="admin-page-header">
    <div class="admin-page-header-info">
        <div class="admin-page-icon" style="background: rgba(249, 115, 22, 0.16); color: #f97316;">
            🏫
        </div>
        <div>
            <div class="admin-page-title-row">
                <h1>Daftar Sekolah / Jenjang</h1>
                <span class="badge-total">{{ $schools->count() }} Unit</span>
            </div>
            <p class="admin-page-desc">Kelola unit pendidikan (PAUD, SD, SMP, SMA) dan situs yayasan</p>
        </div>
    </div>
    <a class="btn btn-add" href="{{ route('admin.schools.create') }}">
        <span>+ Tambah Sekolah Baru</span>
    </a>
</div>

<div class="table-wrapper-card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Nama Sekolah</th>
                    <th>Jenjang</th>
                    <th>Alamat Situs</th>
                    <th>Status</th>
                    <th class="right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($schools as $s)
                    <tr>
                        <td>
                            <strong class="cell-title">{{ $s->name }}</strong>
                        </td>
                        <td>
                            <span class="pill-badge pill-teal">{{ $s->levelLabel() }}</span>
                        </td>
                        <td>
                            @if ($s->is_active)
                                <a href="{{ route('home', $s) }}" target="_blank" rel="noopener" class="cell-link">
                                    {{ route('home', $s) }} ↗
                                </a>
                            @else
                                <span class="pill-badge pill-gray">{{ $s->slug }} (belum aktif)</span>
                            @endif
                        </td>
                        <td>
                            @if ($s->is_active)
                                <span class="pill-badge pill-green"><span class="dot"></span> Aktif</span>
                            @else
                                <span class="pill-badge pill-gray"><span class="dot"></span> Nonaktif</span>
                            @endif
                        </td>
                        <td class="right actions-cell">
                            <div class="actions-group">
                                <a class="btn-action-edit" href="{{ route('admin.schools.edit', $s->slug) }}" title="Ubah Pengaturan Sekolah">
                                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    <span>Ubah</span>
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
