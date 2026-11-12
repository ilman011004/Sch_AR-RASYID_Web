@extends('layouts.admin')

@section('title', 'Ringkasan')

@section('content')
<div class="page-head">
    <h1>Ringkasan <small>· {{ $school->name }}</small></h1>
</div>

@if (! $school->is_active && ! $school->isFoundation())
    <div class="flash bad">Sekolah ini berstatus <strong>nonaktif</strong>, situs publiknya belum bisa dibuka. Aktifkan lewat menu Daftar Sekolah.</div>
@endif

<div class="stat-cards">
    <a class="stat-card card-emerald" href="{{ route('admin.resource.index', 'berita') }}">
        <div class="stat-card-head">
            <span class="stat-icon">📰</span>
            <span class="stat-badge">Aktif</span>
        </div>
        <div class="n">{{ $counts['posts'] }}</div>
        <div class="stat-label">Tulisan Tayang</div>
    </a>

    <a class="stat-card card-amber" href="{{ route('admin.resource.index', 'berita') }}">
        <div class="stat-card-head">
            <span class="stat-icon">⏳</span>
            <span class="stat-badge">Review</span>
        </div>
        <div class="n">{{ $counts['pending'] }}</div>
        <div class="stat-label">Menunggu Persetujuan</div>
    </a>

    <a class="stat-card card-blue" href="{{ route('admin.resource.index', 'galeri') }}">
        <div class="stat-card-head">
            <span class="stat-icon">📸</span>
            <span class="stat-badge">Media</span>
        </div>
        <div class="n">{{ $counts['photos'] }}</div>
        <div class="stat-label">Foto Galeri</div>
    </a>

    @if (in_array(auth()->user()->role, ['super_admin', 'admin'], true))
        <a class="stat-card card-teal" href="{{ auth()->user()->isSuperAdmin() ? route('admin.ppdb.all', ['status' => 'baru']) : route('admin.ppdb.index', ['status' => 'baru']) }}">
            <div class="stat-card-head">
                <span class="stat-icon">🎓</span>
                <span class="stat-badge">PPDB</span>
            </div>
            <div class="n">{{ $counts['new_reg'] }}</div>
            <div class="stat-label">Pendaftar Baru</div>
        </a>

        <a class="stat-card card-rose" href="{{ route('admin.messages.index') }}">
            <div class="stat-card-head">
                <span class="stat-icon">✉️</span>
                <span class="stat-badge">Inbox</span>
            </div>
            <div class="n">{{ $counts['unread'] }}</div>
            <div class="stat-label">Pesan Belum Dibaca</div>
        </a>
    @endif
</div>

<div class="panel">
    <h2>Panduan Mulai Cepat</h2>
    <ol class="todo">
        <li>Lengkapi <a href="{{ route('admin.settings') }}">Pengaturan Sekolah</a> (logo, kepala sekolah, visi misi, kontak).</li>
        <li>Isi <a href="{{ route('admin.resource.index', 'keunggulan') }}">keunggulan</a> dan <a href="{{ route('admin.resource.index', 'faq') }}">FAQ</a> untuk beranda.</li>
        <li>Tambahkan <a href="{{ route('admin.resource.index', 'guru') }}">guru</a> dan <a href="{{ route('admin.resource.index', 'program') }}">program akademik</a>.</li>
        <li>Tulis <a href="{{ route('admin.resource.index', 'berita') }}">berita</a> dan unggah <a href="{{ route('admin.resource.index', 'galeri') }}">foto galeri</a>.</li>
        <li>Atur jadwal dan informasi PPDB di Pengaturan Sekolah, lalu buka pendaftaran.</li>
    </ol>
</div>
@endsection
