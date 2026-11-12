@extends('layouts.admin')

@section('title', 'Akun Pengguna')

@section('content')
<div class="admin-page-header">
    <div class="admin-page-header-info">
        <div class="admin-page-icon" style="background: rgba(139, 92, 246, 0.16); color: #8b5cf6;">
            👥
        </div>
        <div>
            <div class="admin-page-title-row">
                <h1>Akun Pengguna</h1>
                <span class="badge-total">{{ $users->count() }} Akun</span>
            </div>
            <p class="admin-page-desc">Kelola hak akses akun administrator, operator, dan pengelola sekolah</p>
        </div>
    </div>
    <a class="btn btn-add" href="{{ route('admin.users.create') }}">
        <span>+ Tambah Akun Baru</span>
    </a>
</div>

<div class="table-wrapper-card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Peran</th>
                    <th>Sekolah</th>
                    <th>Status</th>
                    <th class="right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $u)
                    <tr>
                        <td>
                            <strong class="cell-title">{{ $u->name }}</strong>
                        </td>
                        <td><span class="cell-text">{{ $u->email }}</span></td>
                        <td>
                            <span class="pill-badge pill-purple">{{ $u->roleLabel() }}</span>
                        </td>
                        <td>
                            <span class="cell-text">{{ $u->school?->name ?? 'Semua Sekolah' }}</span>
                        </td>
                        <td>
                            @if ($u->is_active)
                                <span class="pill-badge pill-green"><span class="dot"></span> Aktif</span>
                            @else
                                <span class="pill-badge pill-gray"><span class="dot"></span> Nonaktif</span>
                            @endif
                        </td>
                        <td class="right actions-cell">
                            <div class="actions-group">
                                <a class="btn-action-edit" href="{{ route('admin.users.edit', $u) }}" title="Ubah Akun">
                                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    <span>Ubah</span>
                                </a>
                                @unless ($u->is(auth()->user()))
                                    <form method="post" action="{{ route('admin.users.destroy', $u) }}" onsubmit="return confirm('Hapus akun ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-action-delete" title="Hapus Akun">
                                            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            <span>Hapus</span>
                                        </button>
                                    </form>
                                @endunless
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
