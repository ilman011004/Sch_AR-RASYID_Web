@extends('layouts.admin')

@section('title', $target ? 'Ubah Akun' : 'Tambah Akun')

@section('content')
@php
    $isSuper = auth()->user()->isSuperAdmin();
    $editingSelf = $target && $target->is(auth()->user());
@endphp
<div class="page-head">
    <h1>{{ $target ? 'Ubah' : 'Tambah' }} Akun</h1>
    <a href="{{ route('admin.users.index') }}">← Kembali</a>
</div>

<form method="post" class="panel"
      action="{{ $target ? route('admin.users.update', $target) : route('admin.users.store') }}">
    @csrf
    @if ($target) @method('PUT') @endif

    <div class="field">
        <label for="name">Nama</label>
        <input id="name" name="name" value="{{ old('name', $target?->name) }}" required maxlength="150">
    </div>

    <div class="field">
        <label for="email">Email (untuk masuk)</label>
        <input id="email" type="email" name="email" value="{{ old('email', $target?->email) }}" required maxlength="150">
    </div>

    @if ($isSuper && ! $editingSelf)
        <div class="field">
            <label for="role">Peran</label>
            <select id="role" name="role">
                @foreach ($roles as $key => $label)
                    <option value="{{ $key }}" @selected(old('role', $target?->role ?? 'operator') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="field">
            <label for="school_id">Sekolah</label>
            <select id="school_id" name="school_id">
                <option value="">— (Super Admin: semua sekolah) —</option>
                @foreach ($schools as $s)
                    <option value="{{ $s->id }}" @selected((string) old('school_id', $target?->school_id) === (string) $s->id)>{{ $s->name }}</option>
                @endforeach
            </select>
            <small>Wajib dipilih untuk Admin Sekolah dan Operator.</small>
        </div>
    @else
        <p class="muted small">Peran: <strong>{{ $target?->roleLabel() ?? 'Operator / Guru' }}</strong></p>
    @endif

    <div class="field">
        <label for="password">Kata sandi {{ $target ? '(kosongkan jika tidak diubah)' : '' }}</label>
        <input id="password" type="password" name="password" autocomplete="new-password" {{ $target ? '' : 'required' }}>
        <small>Minimal 10 karakter.</small>
    </div>

    <div class="field">
        <label for="password_confirmation">Ulangi kata sandi</label>
        <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password">
    </div>

    @unless ($editingSelf)
        <div class="field">
            <input type="hidden" name="is_active" value="0">
            <label class="check">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $target?->is_active ?? true))>
                Akun aktif
            </label>
        </div>
    @endunless

    <button type="submit" class="btn">Simpan</button>
</form>
@endsection