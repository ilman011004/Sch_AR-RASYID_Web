@extends('layouts.admin')

@section('title', 'Ringkasan')

@section('content')
<h1>Ringkasan — {{ $school->name }}</h1>

@if (! $school->is_active && ! $school->isFoundation())
    <div class="flash bad">Sekolah ini berstatus <strong>nonaktif</strong>, situs publiknya belum bisa dibuka. Aktifkan lewat menu Daftar Sekolah.</div>
@endif

<div class="stat-cards">
    <div class="stat-card"><div class="n">{{ $counts['posts'] }}</div>Tulisan tayang</div>
    <div class="stat-card"><div class="n">{{ $counts['pending'] }}</div>Menunggu persetujuan</div>
    <div class="stat-card"><div class="n">{{ $counts['photos'] }}</div>Foto galeri</div>

    @if (in_array(auth()->user()->role, ['super_admin', 'admin'], true))
        <a class="stat-card" href="{{ route('admin.ppdb.index', ['status' => 'baru']) }}"><div class="n">{{ $counts['new_reg'] }}</div>Pendaftar baru</a>
        <a class="stat-card" href="{{ route('admin.messages.index') }}"><div class="n">{{ $counts['unread'] }}</div>Pesan belum dibaca</a>
    @endif
</div>

<h2>Mulai dari sini</h2>
<ol class="todo">
    <li>Lengkapi <a href="{{ route('admin.settings') }}">Pengaturan Sekolah</a> (logo, kepala sekolah, visi misi, kontak).</li>
    <li>Isi <a href="{{ route('admin.resource.index', 'keunggulan') }}">keunggulan</a> dan <a href="{{ route('admin.resource.index', 'faq') }}">FAQ</a> untuk beranda.</li>
    <li>Tambahkan <a href="{{ route('admin.resource.index', 'guru') }}">guru</a> dan <a href="{{ route('admin.resource.index', 'program') }}">program akademik</a>.</li>
    <li>Tulis <a href="{{ route('admin.resource.index', 'berita') }}">berita</a> dan unggah <a href="{{ route('admin.resource.index', 'galeri') }}">foto galeri</a>.</li>
    <li>Atur jadwal dan informasi PPDB di Pengaturan Sekolah, lalu buka pendaftaran.</li>
</ol>
@endsection