@extends('layouts.admin')

@section('title', $d['label'])

@section('content')
@php
    $icons = [
        'slider' => ['icon' => '🖼️', 'desc' => 'Kelola foto dan video tayangan beranda situs sekolah', 'color' => '#f43f5e'],
        'berita' => ['icon' => '📰', 'desc' => 'Kelola publikasi artikel berita dan pengumuman sekolah', 'color' => '#10b981'],
        'galeri' => ['icon' => '📸', 'desc' => 'Dokumentasi foto kegiatan, acara, dan prestasi siswa', 'color' => '#f59e0b'],
        'guru' => ['icon' => '👨‍🏫', 'desc' => 'Data tenaga pendidik dan kependidikan sekolah', 'color' => '#3b82f6'],
        'program' => ['icon' => '⭐', 'desc' => 'Daftar program unggulan dan ekstrakurikuler sekolah', 'color' => '#a855f7'],
        'keunggulan' => ['icon' => '🏆', 'desc' => 'Poin-poin keunggulan dan keistimewaan sekolah', 'color' => '#eab308'],
        'faq' => ['icon' => '💬', 'desc' => 'Pertanyaan umum yang sering ditanyakan orang tua / siswa', 'color' => '#06b6d4'],
    ];
    $meta = $icons[$resource] ?? ['icon' => '📁', 'desc' => 'Kelola data '.$d['label'], 'color' => '#0f766e'];
    $maps = collect($d['fields'])->where('type', 'select')->mapWithKeys(fn ($f) => [$f['name'] => $f['options']]);
@endphp

{{-- Header Banner Modern --}}
<div class="admin-page-header">
    <div class="admin-page-header-info">
        <div class="admin-page-icon" style="background: {{ $meta['color'] }}18; color: {{ $meta['color'] }};">
            {{ $meta['icon'] }}
        </div>
        <div>
            <div class="admin-page-title-row">
                <h1>{{ $d['label'] }}</h1>
                <span class="badge-total">{{ $items->total() }} Data</span>
            </div>
            <p class="admin-page-desc">{{ $meta['desc'] }}</p>
        </div>
    </div>
    <a class="btn btn-add" href="{{ route('admin.resource.create', $resource) }}">
        <span>+ Tambah {{ $d['label'] }}</span>
    </a>
</div>

{{-- Container Card untuk Tabel --}}
<div class="table-wrapper-card">
    @if ($items->isNotEmpty())
        <div class="table-card-topbar">
            <div class="table-search-box">
                <span class="search-icon">🔍</span>
                <input type="text" id="adminSearchInput" onkeyup="filterAdminRows()" placeholder="Ketik untuk mencari di tabel...">
            </div>
            <div class="table-card-stat">
                Menampilkan <strong>{{ $items->count() }}</strong> dari <strong>{{ $items->total() }}</strong> data
            </div>
        </div>
    @endif

    <div class="table-responsive">
        <table class="table" id="adminDataTable">
            <thead>
                <tr>
                    @foreach ($d['columns'] as $col => $label)
                        <th class="{{ $col === 'sort' ? 'center' : '' }}">{{ $label }}</th>
                    @endforeach
                    <th class="right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    <tr>
                        @foreach ($d['columns'] as $col => $label)
                            @php
                                $val = $item->{$col};
                                $rawVal = $val;
                                if ($val instanceof \DateTimeInterface) { 
                                    $val = $val->format('d/m/Y H:i'); 
                                } elseif (isset($maps[$col])) { 
                                    $val = $maps[$col][$val] ?? $val; 
                                }
                            @endphp

                            <td class="{{ $col === 'sort' ? 'center' : '' }}">
                                {{-- Thumbnail untuk judul/nama jika ada foto --}}
                                @if (in_array($col, ['title', 'name']) && ($item->image || $item->photo))
                                    <div class="cell-media">
                                        <img src="{{ asset('storage/'.($item->image ?? $item->photo)) }}" alt="" class="cell-thumb" loading="lazy">
                                        <div>
                                            <strong class="cell-title">{{ \Illuminate\Support\Str::limit((string) $val, 70) }}</strong>
                                        </div>
                                    </div>
                                @elseif ($col === 'status')
                                    @if ($rawVal === 'published' || $rawVal === 'Terbitkan')
                                        <span class="pill-badge pill-green"><span class="dot"></span> Terbitkan</span>
                                    @elseif ($rawVal === 'pending' || $rawVal === 'Menunggu persetujuan')
                                        <span class="pill-badge pill-yellow"><span class="dot"></span> Menunggu</span>
                                    @else
                                        <span class="pill-badge pill-gray"><span class="dot"></span> Draf</span>
                                    @endif
                                @elseif ($col === 'type')
                                    @if ($rawVal === 'berita')
                                        <span class="pill-badge pill-blue">📰 Berita kegiatan</span>
                                    @elseif ($rawVal === 'pengumuman')
                                        <span class="pill-badge pill-amber">📢 Pengumuman</span>
                                    @elseif ($rawVal === 'foto')
                                        <span class="pill-badge pill-rose">🖼️ Foto</span>
                                    @elseif ($rawVal === 'video')
                                        <span class="pill-badge pill-purple">🎬 Video</span>
                                    @else
                                        <span class="pill-badge pill-slate">{{ $val }}</span>
                                    @endif
                                @elseif ($col === 'category' || $col === 'staff_group')
                                    <span class="pill-badge pill-teal">{{ $val }}</span>
                                @elseif ($col === 'sort')
                                    <span class="pill-badge pill-neutral">#{{ $val }}</span>
                                @else
                                    <span class="cell-text">{{ \Illuminate\Support\Str::limit((string) $val, 80) }}</span>
                                @endif
                            </td>
                        @endforeach

                        <td class="right actions-cell">
                            <div class="actions-group">
                                <a class="btn-action-edit" href="{{ route('admin.resource.edit', [$resource, $item->id]) }}" title="Ubah data">
                                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    <span>Ubah</span>
                                </a>
                                <form method="post" action="{{ route('admin.resource.destroy', [$resource, $item->id]) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-action-delete" title="Hapus data">
                                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        <span>Hapus</span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($d['columns']) + 1 }}">
                            <div class="empty-state-card">
                                <div class="empty-icon">{{ $meta['icon'] }}</div>
                                <h3>Belum Ada {{ $d['label'] }}</h3>
                                <p>Mulai tambahkan konten {{ strtolower($d['label']) }} untuk ditampilkan pada website.</p>
                                <a class="btn btn-add" href="{{ route('admin.resource.create', $resource) }}">
                                    + Tambah {{ $d['label'] }} Baru
                                </a>
                            </div>
                        </td>
                    </tr>
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

<script>
function filterAdminRows() {
    var input = document.getElementById("adminSearchInput");
    var filter = input.value.toLowerCase();
    var table = document.getElementById("adminDataTable");
    var tr = table.getElementsByTagName("tr");
    for (var i = 1; i < tr.length; i++) {
        var rowText = tr[i].innerText.toLowerCase();
        tr[i].style.display = rowText.indexOf(filter) > -1 ? "" : "none";
    }
}
</script>
@endsection
