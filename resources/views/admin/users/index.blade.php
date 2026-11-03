@extends('layouts.admin')

@section('title', 'Akun Pengguna')

@section('content')
<div class="page-head">
    <h1>Akun Pengguna</h1>
    <a class="btn" href="{{ route('admin.users.create') }}">+ Tambah</a>
</div>

@unless (auth()->user()->isSuperAdmin())
    <p class="muted small">Sebagai admin sekolah, Anda dapat membuat dan mengelola akun operator/guru untuk sekolah ini.</p>
@endunless

<table class="table">
    <thead><tr><th>Nama</th><th>Email</th><th>Peran</th><th>Sekolah</th><th>Status</th><th class="right">Aksi</th></tr></thead>
    <tbody>
        @foreach ($users as $u)
            <tr>
                <td>{{ $u->name }}</td>
                <td>{{ $u->email }}</td>
                <td>{{ $u->roleLabel() }}</td>
                <td>{{ $u->school?->name ?? 'Semua' }}</td>
                <td>{{ $u->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                <td class="right actions">
                    <a href="{{ route('admin.users.edit', $u) }}">Ubah</a>
                    @unless ($u->is(auth()->user()))
                        <form method="post" action="{{ route('admin.users.destroy', $u) }}" onsubmit="return confirm('Hapus akun ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="link-btn danger">Hapus</button>
                        </form>
                    @endunless
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
@endsection