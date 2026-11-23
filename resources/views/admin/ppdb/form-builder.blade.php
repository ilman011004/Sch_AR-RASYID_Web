@extends('layouts.admin')

@section('title', 'Pengaturan Formulir PPDB — '.$school->name)

@section('content')
<style>
    .fb-container { max-width: 980px; margin-inline: auto; }
    
    /* Hero Header */
    .fb-hero {
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 14px;
        padding: 1.5rem 1.75rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 10px rgba(0,0,0,.03);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .fb-hero-title { margin: 0; font-size: 1.35rem; font-weight: 800; color: #0f172a; }
    .fb-hero-desc { margin: .35rem 0 0; color: #64748b; font-size: .88rem; }

    /* Empty state */
    .fb-empty {
        background: #fff;
        border: 2px dashed #cbd5e1;
        border-radius: 16px;
        padding: 3.5rem 2rem;
        text-align: center;
        margin-bottom: 2rem;
    }
    .fb-empty-icon { font-size: 3rem; margin-bottom: .8rem; }
    .fb-empty-title { font-size: 1.2rem; font-weight: 800; color: #1e293b; margin-bottom: .4rem; }
    .fb-empty-desc { color: #64748b; font-size: .9rem; max-width: 440px; margin: 0 auto 1.5rem; }

    /* Page Card (List Item) */
    .fb-page-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        margin-bottom: 1.25rem;
        box-shadow: 0 3px 12px rgba(0,0,0,.03);
        transition: all .2s ease;
        overflow: hidden;
    }
    .fb-page-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 6px 20px rgba(0,0,0,.06);
    }
    .fb-page-card.is-open {
        border-color: var(--primary);
        box-shadow: 0 8px 24px rgba(15, 118, 110, .09);
    }

    /* Page Card Header */
    .fb-page-header {
        padding: 1.15rem 1.4rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        background: #fff;
        cursor: pointer;
        user-select: none;
    }
    .fb-page-info {
        display: flex;
        align-items: center;
        gap: 1rem;
        flex: 1;
        min-width: 0;
    }
    .fb-page-badge {
        background: #f1f5f9;
        color: #475569;
        font-weight: 800;
        font-size: .78rem;
        padding: .4rem .75rem;
        border-radius: 8px;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        border: 1px solid #e2e8f0;
    }
    .fb-page-card.is-open .fb-page-badge {
        background: color-mix(in srgb, var(--primary) 12%, #fff);
        color: var(--primary);
        border-color: color-mix(in srgb, var(--primary) 30%, #fff);
    }
    .fb-page-meta {
        min-width: 0;
        flex: 1;
    }
    .fb-page-name {
        font-weight: 800;
        font-size: 1.05rem;
        color: #0f172a;
        margin: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .fb-page-subtext {
        font-size: .8rem;
        color: #64748b;
        margin: .2rem 0 0;
        display: flex;
        gap: 1rem;
        align-items: center;
    }
    .fb-page-actions {
        display: flex;
        align-items: center;
        gap: .5rem;
    }
    .fb-btn-action {
        border: 1px solid #e2e8f0;
        background: #fff;
        color: #334155;
        border-radius: 8px;
        padding: .45rem .85rem;
        font-size: .82rem;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        transition: all .15s ease;
    }
    .fb-btn-action:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
    }
    .fb-btn-edit {
        background: #f8fafc;
        color: var(--primary);
        border-color: #cbd5e1;
    }
    .fb-btn-edit:hover {
        background: color-mix(in srgb, var(--primary) 10%, #fff);
        border-color: var(--primary);
    }
    .fb-btn-delete {
        color: #dc2626;
        border-color: #fecaca;
        background: #fff;
    }
    .fb-btn-delete:hover {
        background: #fee2e2;
        border-color: #fca5a5;
    }

    /* Page Body / Editor (Collapsible) */
    .fb-page-body {
        border-top: 1px solid #e2e8f0;
        padding: 1.5rem 1.4rem;
        background: #f8fafc;
        display: none;
    }
    .fb-page-card.is-open .fb-page-body {
        display: block;
    }

    /* Page Meta Inputs */
    .fb-page-inputs {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1.25rem;
        margin-bottom: 1.25rem;
    }
    .fb-page-inputs label {
        font-size: .8rem;
        font-weight: 700;
        color: #475569;
        margin-bottom: .35rem;
        display: block;
    }

    /* Question Cards */
    .fb-q-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        margin-bottom: 1.25rem;
    }
    .fb-q-card {
        background: #fff;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        padding: 1.25rem;
        box-shadow: 0 2px 6px rgba(0,0,0,.03);
        position: relative;
        transition: all .15s;
    }
    .fb-q-card:focus-within {
        border-color: var(--primary);
        box-shadow: 0 4px 14px rgba(15, 118, 110, .09);
    }
    .fb-q-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: .85rem;
    }
    .fb-q-number {
        font-size: .8rem;
        font-weight: 800;
        color: #64748b;
        display: inline-flex;
        align-items: center;
        gap: .35rem;
    }
    .fb-q-grid {
        display: grid;
        grid-template-columns: 1.7fr 1.1fr;
        gap: 1rem;
        align-items: start;
    }
    @media (max-width: 768px) {
        .fb-q-grid { grid-template-columns: 1fr; }
    }
    .fb-options-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: .85rem;
        margin-top: .85rem;
    }
    .fb-options-box label {
        font-size: .8rem;
        font-weight: 700;
        color: #1e293b;
        display: block;
        margin-bottom: .3rem;
    }
    .fb-options-box small {
        font-size: .75rem;
        color: #64748b;
        display: block;
        margin-bottom: .4rem;
    }
    .fb-q-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-top: 1px solid #f1f5f9;
        margin-top: 1rem;
        padding-top: .75rem;
    }

    /* Buttons */
    .fb-btn-add-q {
        background: #fff;
        border: 2px dashed var(--primary);
        color: var(--primary);
        font-weight: 800;
        padding: .75rem 1rem;
        border-radius: 10px;
        cursor: pointer;
        font-size: .88rem;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: .45rem;
        transition: all .15s;
    }
    .fb-btn-add-q:hover {
        background: color-mix(in srgb, var(--primary) 8%, #fff);
    }

    .fb-btn-add-page-main {
        background: #0f172a;
        color: #fff;
        border: none;
        border-radius: 10px;
        padding: .7rem 1.3rem;
        font-weight: 700;
        font-size: .9rem;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        transition: background .15s;
    }
    .fb-btn-add-page-main:hover {
        background: #1e293b;
    }

    .fb-sticky-save {
        position: sticky;
        bottom: 1.5rem;
        z-index: 30;
        background: rgba(255, 255, 255, .95);
        backdrop-filter: blur(8px);
        border: 1px solid var(--line);
        border-radius: 14px;
        padding: 1rem 1.5rem;
        box-shadow: 0 10px 30px rgba(0,0,0,.12);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        margin-top: 2rem;
    }
</style>

<div class="fb-container">
    {{-- Hero Section --}}
    <div class="fb-hero">
        <div>
            <h1 class="fb-hero-title">Pengaturan Formulir PPDB <span style="font-weight:400;color:#64748b">({{ $school->name }})</span></h1>
            <p class="fb-hero-desc">Atur struktur halaman dan daftar pertanyaan pendaftaran santri baru secara mandiri.</p>
        </div>
        <div style="display:flex;gap:.6rem;align-items:center">
            <button type="button" class="fb-btn-add-page-main" onclick="addNewPage()">
                <span>➕</span> Tambah Halaman
            </button>
            <form method="post" action="{{ route('admin.ppdb.form-builder.reset') }}" onsubmit="return confirm('Kembalikan susunan formulir ke default?')">
                @csrf
                <button type="submit" class="fb-btn-action" style="color:#64748b;font-weight:600">
                    ↺ Reset Default
                </button>
            </form>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success" style="margin-bottom: 1.5rem">
            {{ session('status') }}
        </div>
    @endif

    <form method="post" action="{{ route('admin.ppdb.form-builder.update') }}" id="fbMainForm">
        @csrf @method('PUT')

        {{-- Container List Halaman --}}
        <div id="pagesListContainer">
            @forelse ($schema['pages'] as $pIdx => $page)
                <div class="fb-page-card {{ $pIdx === 0 ? 'is-open' : '' }}" data-page-index="{{ $pIdx }}">
                    {{-- Tampak Depan (Halaman Baris) --}}
                    <div class="fb-page-header" onclick="togglePageCard(this)">
                        <div class="fb-page-info">
                            <span class="fb-page-badge">
                                📑 Halaman <span class="page-num-display">{{ $pIdx + 1 }}</span>
                            </span>
                            <div class="fb-page-meta">
                                <h3 class="fb-page-name page-title-preview">{{ $page['title'] }}</h3>
                                <p class="fb-page-subtext">
                                    <span class="page-q-count">{{ count($page['fields'] ?? []) }} pertanyaan</span>
                                    @if (!empty($page['subtitle']))
                                        <span>•</span>
                                        <span class="page-subtitle-preview">{{ Str::limit($page['subtitle'], 40) }}</span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="fb-page-actions" onclick="event.stopPropagation()">
                            <button type="button" class="fb-btn-action fb-btn-edit" onclick="toggleEditPage(this)">
                                <span class="btn-edit-text">{{ $pIdx === 0 ? 'Tutup' : 'Edit Halaman' }}</span>
                                <span class="btn-edit-icon">{{ $pIdx === 0 ? '▲' : '✏️' }}</span>
                            </button>
                            <button type="button" class="fb-btn-action fb-btn-delete" onclick="deletePageCard(this)" title="Hapus Halaman Ini">
                                🗑️ Hapus
                            </button>
                        </div>
                    </div>

                    {{-- Form Editor Halaman & Pertanyaan (Terbuka saat diedit) --}}
                    <div class="fb-page-body">
                        {{-- Pengaturan Nama & Keterangan Halaman --}}
                        <div class="fb-page-inputs">
                            <div style="margin-bottom:.85rem">
                                <label>Nama / Judul Halaman *</label>
                                <input type="text" name="pages[{{ $pIdx }}][title]" value="{{ $page['title'] }}" required
                                       placeholder="Contoh: Biodata Calon Santri"
                                       oninput="updatePageTitlePreview(this)"
                                       style="font-size:1.05rem;font-weight:700">
                            </div>
                            <div>
                                <label>Petunjuk / Deskripsi Tambahan (Opsional)</label>
                                <input type="text" name="pages[{{ $pIdx }}][subtitle]" value="{{ $page['subtitle'] ?? '' }}"
                                       placeholder="Contoh: Lengkapi data identitas di bawah ini sesuai kartu keluarga"
                                       oninput="updatePageSubtitlePreview(this)">
                            </div>
                        </div>

                        {{-- Daftar Pertanyaan --}}
                        <div style="margin-bottom:.85rem;display:flex;justify-content:space-between;align-items:center">
                            <strong style="font-size:.9rem;color:#1e293b">Daftar Pertanyaan:</strong>
                            <small class="muted">Bebas tambah, edit, atau hapus pertanyaan pada halaman ini</small>
                        </div>

                        <div class="fb-q-list">
                            @foreach ($page['fields'] ?? [] as $fIdx => $f)
                                <div class="fb-q-card" data-q-index="{{ $fIdx }}">
                                    <input type="hidden" name="pages[{{ $pIdx }}][fields][{{ $fIdx }}][id]" value="{{ $f['id'] }}">
                                    <input type="hidden" name="pages[{{ $pIdx }}][fields][{{ $fIdx }}][mapping]" value="{{ $f['mapping'] ?? '' }}">

                                    <div class="fb-q-header">
                                        <span class="fb-q-number">Pertanyaan <span class="q-num-display">{{ $fIdx + 1 }}</span></span>
                                        <button type="button" onclick="deleteQuestion(this)" class="fb-btn-action fb-btn-delete" style="padding:.25rem .55rem;font-size:.78rem">
                                            🗑️ Hapus Pertanyaan
                                        </button>
                                    </div>

                                    <div class="fb-q-grid">
                                        <div>
                                            <label style="font-size:.78rem;font-weight:700;color:#64748b;display:block;margin-bottom:.3rem">Teks Pertanyaan *</label>
                                            <input type="text" name="pages[{{ $pIdx }}][fields][{{ $fIdx }}][label]" value="{{ $f['label'] }}" required placeholder="Contoh: Nama Lengkap Siswa">
                                            <input type="text" name="pages[{{ $pIdx }}][fields][{{ $fIdx }}][help]" value="{{ $f['help'] ?? '' }}" placeholder="Petunjuk pengisian (opsional)" style="margin-top:.4rem;font-size:.82rem;padding:.4rem .65rem">
                                        </div>
                                        <div>
                                            <label style="font-size:.78rem;font-weight:700;color:#64748b;display:block;margin-bottom:.3rem">Tipe Jawaban *</label>
                                            <select name="pages[{{ $pIdx }}][fields][{{ $fIdx }}][type]" onchange="toggleOptionsBox(this)">
                                                <option value="text" @selected(($f['type'] ?? 'text') === 'text')>Jawaban Singkat (Teks)</option>
                                                <option value="paragraph" @selected(($f['type'] ?? '') === 'paragraph')>Paragraf (Teks Panjang)</option>
                                                <option value="number" @selected(($f['type'] ?? '') === 'number')>Angka / Nomor</option>
                                                <option value="date" @selected(($f['type'] ?? '') === 'date')>Tanggal</option>
                                                <option value="radio" @selected(($f['type'] ?? '') === 'radio')>Pilihan Ganda (Pilih 1)</option>
                                                <option value="checkbox" @selected(($f['type'] ?? '') === 'checkbox')>Kotak Centang (Pilih Banyak)</option>
                                                <option value="select" @selected(($f['type'] ?? '') === 'select')>Dropdown (Pilihan Menu)</option>
                                                <option value="file" @selected(($f['type'] ?? '') === 'file')>Upload Berkas (File/Dokumen)</option>
                                            </select>
                                        </div>
                                    </div>

                                    @php
                                        $showOpts = in_array($f['type'] ?? '', ['radio', 'checkbox', 'select']);
                                        $optionsText = is_array($f['options'] ?? null) ? implode("\n", $f['options']) : ($f['options'] ?? '');
                                    @endphp
                                    <div class="fb-options-box" style="{{ $showOpts ? '' : 'display:none;' }}">
                                        <label>Daftar Opsi Pilihan (Tulis 1 pilihan per baris):</label>
                                        <small>Contoh:<br>Laki-laki<br>Perempuan</small>
                                        <textarea name="pages[{{ $pIdx }}][fields][{{ $fIdx }}][options]" rows="3" placeholder="Pilihan 1&#10;Pilihan 2&#10;Pilihan 3">{{ $optionsText }}</textarea>
                                    </div>

                                    <div class="fb-q-footer">
                                        <label style="cursor:pointer;font-size:.85rem;font-weight:700;display:inline-flex;align-items:center;gap:.45rem;color:#334155">
                                            <input type="checkbox" name="pages[{{ $pIdx }}][fields][{{ $fIdx }}][required]" value="1" @checked(!empty($f['required']))>
                                            <span>Wajib Diisi oleh Pendaftar</span>
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Tombol Tambah Pertanyaan di Halaman Ini --}}
                        <button type="button" class="fb-btn-add-q" onclick="addNewQuestion(this)">
                            <span>➕</span> Tambah Pertanyaan di Halaman Ini
                        </button>
                    </div>
                </div>
            @empty
                {{-- Kosong --}}
            @endforelse
        </div>

        {{-- State Bila Tidak Ada Halaman --}}
        <div id="fbEmptyState" class="fb-empty" style="{{ count($schema['pages'] ?? []) > 0 ? 'display:none;' : '' }}">
            <div class="fb-empty-icon">📑</div>
            <h3 class="fb-empty-title">Belum Ada Halaman Formulir</h3>
            <p class="fb-empty-desc">Klik tombol "Tambah Halaman" di bawah ini untuk mulai membuat halaman pertama formulir pendaftaran PPDB Anda.</p>
            <button type="button" class="fb-btn-add-page-main" onclick="addNewPage()">
                <span>➕</span> Tambah Halaman Pertama
            </button>
        </div>

        {{-- Sticky Action Bar --}}
        <div class="fb-sticky-save" id="fbSaveBar" style="{{ count($schema['pages'] ?? []) > 0 ? '' : 'display:none;' }}">
            <div>
                <strong style="color:#0f172a;display:block">Simpan Perubahan Formulir</strong>
                <small class="muted">Pastikan semua judul halaman dan pertanyaan sudah sesuai.</small>
            </div>
            <div style="display:flex;gap:.8rem;align-items:center">
                <button type="button" class="fb-btn-action" onclick="addNewPage()">
                    <span>➕</span> Tambah Halaman Baru
                </button>
                <button type="submit" class="btn" style="padding:.75rem 1.8rem;font-weight:800;font-size:.95rem">
                    Simpan Formulir
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    // Buka / Tutup Accordion saat baris header diklik
    function togglePageCard(header) {
        const card = header.closest('.fb-page-card');
        const isOpen = card.classList.contains('is-open');
        
        if (isOpen) {
            card.classList.remove('is-open');
            card.querySelector('.btn-edit-text').textContent = 'Edit Halaman';
            card.querySelector('.btn-edit-icon').textContent = '✏️';
        } else {
            card.classList.add('is-open');
            card.querySelector('.btn-edit-text').textContent = 'Tutup';
            card.querySelector('.btn-edit-icon').textContent = '▲';
        }
    }

    // Tombol Edit Halaman di sebelah kanan
    function toggleEditPage(btn) {
        const card = btn.closest('.fb-page-card');
        const isOpen = card.classList.contains('is-open');
        if (isOpen) {
            card.classList.remove('is-open');
            btn.querySelector('.btn-edit-text').textContent = 'Edit Halaman';
            btn.querySelector('.btn-edit-icon').textContent = '✏️';
        } else {
            card.classList.add('is-open');
            btn.querySelector('.btn-edit-text').textContent = 'Tutup';
            btn.querySelector('.btn-edit-icon').textContent = '▲';
        }
    }

    // Update Live Title Preview saat admin mengetik nama halaman
    function updatePageTitlePreview(input) {
        const card = input.closest('.fb-page-card');
        const preview = card.querySelector('.page-title-preview');
        preview.textContent = input.value.trim() || 'Halaman Tanpa Judul';
    }

    // Update Live Subtitle Preview
    function updatePageSubtitlePreview(input) {
        const card = input.closest('.fb-page-card');
        let preview = card.querySelector('.page-subtitle-preview');
        if (!preview) {
            const subtext = card.querySelector('.fb-page-subtext');
            subtext.insertAdjacentHTML('beforeend', `<span>•</span> <span class="page-subtitle-preview"></span>`);
            preview = card.querySelector('.page-subtitle-preview');
        }
        preview.textContent = input.value.trim();
    }

    // Tampilkan / Sembunyikan Pilihan Opsi untuk Radio, Checkbox, Select
    function toggleOptionsBox(select) {
        const qCard = select.closest('.fb-q-card');
        const box = qCard.querySelector('.fb-options-box');
        if (['radio', 'checkbox', 'select'].includes(select.value)) {
            box.style.display = 'block';
        } else {
            box.style.display = 'none';
        }
    }

    // Sinkronisasi index form fields & label nomor
    function reindexPagesAndQuestions() {
        const pages = document.querySelectorAll('.fb-page-card');
        const emptyState = document.getElementById('fbEmptyState');
        const saveBar = document.getElementById('fbSaveBar');

        if (pages.length === 0) {
            emptyState.style.display = 'block';
            saveBar.style.display = 'none';
            return;
        } else {
            emptyState.style.display = 'none';
            saveBar.style.display = 'flex';
        }

        pages.forEach((page, pIdx) => {
            page.setAttribute('data-page-index', pIdx);
            const numDisplay = page.querySelector('.page-num-display');
            if (numDisplay) numDisplay.textContent = pIdx + 1;

            // Reindex inputs yang terikat ke pages[pIdx]
            page.querySelectorAll('input, select, textarea').forEach(input => {
                const name = input.getAttribute('name');
                if (name && name.startsWith('pages[')) {
                    input.setAttribute('name', name.replace(/pages\[\d+\]/, `pages[${pIdx}]`));
                }
            });

            // Reindex pertanyaan dalam page ini
            const questions = page.querySelectorAll('.fb-q-card');
            const qCount = page.querySelector('.page-q-count');
            if (qCount) qCount.textContent = `${questions.length} pertanyaan`;

            questions.forEach((q, fIdx) => {
                q.setAttribute('data-q-index', fIdx);
                const qNum = q.querySelector('.q-num-display');
                if (qNum) qNum.textContent = fIdx + 1;

                q.querySelectorAll('input, select, textarea').forEach(input => {
                    const name = input.getAttribute('name');
                    if (name && name.includes('][fields][')) {
                        input.setAttribute('name', name.replace(/\[fields\]\[\d+\]/, `[fields][${fIdx}]`));
                    }
                });
            });
        });
    }

    // Tambah Halaman Baru
    function addNewPage() {
        const container = document.getElementById('pagesListContainer');
        const pIdx = document.querySelectorAll('.fb-page-card').length;
        const pageNum = pIdx + 1;

        const html = `
            <div class="fb-page-card is-open" data-page-index="${pIdx}">
                <div class="fb-page-header" onclick="togglePageCard(this)">
                    <div class="fb-page-info">
                        <span class="fb-page-badge">
                            📑 Halaman <span class="page-num-display">${pageNum}</span>
                        </span>
                        <div class="fb-page-meta">
                            <h3 class="fb-page-name page-title-preview">Halaman ${pageNum}</h3>
                            <p class="fb-page-subtext">
                                <span class="page-q-count">0 pertanyaan</span>
                            </p>
                        </div>
                    </div>
                    <div class="fb-page-actions" onclick="event.stopPropagation()">
                        <button type="button" class="fb-btn-action fb-btn-edit" onclick="toggleEditPage(this)">
                            <span class="btn-edit-text">Tutup</span>
                            <span class="btn-edit-icon">▲</span>
                        </button>
                        <button type="button" class="fb-btn-action fb-btn-delete" onclick="deletePageCard(this)" title="Hapus Halaman Ini">
                            🗑️ Hapus
                        </button>
                    </div>
                </div>

                <div class="fb-page-body">
                    <div class="fb-page-inputs">
                        <div style="margin-bottom:.85rem">
                            <label>Nama / Judul Halaman *</label>
                            <input type="text" name="pages[${pIdx}][title]" value="Halaman ${pageNum}" required
                                   placeholder="Contoh: Biodata Calon Santri"
                                   oninput="updatePageTitlePreview(this)"
                                   style="font-size:1.05rem;font-weight:700">
                        </div>
                        <div>
                            <label>Petunjuk / Deskripsi Tambahan (Opsional)</label>
                            <input type="text" name="pages[${pIdx}][subtitle]" value=""
                                   placeholder="Contoh: Lengkapi data di bawah ini dengan benar"
                                   oninput="updatePageSubtitlePreview(this)">
                        </div>
                    </div>

                    <div style="margin-bottom:.85rem;display:flex;justify-content:space-between;align-items:center">
                        <strong style="font-size:.9rem;color:#1e293b">Daftar Pertanyaan:</strong>
                        <small class="muted">Tambahkan pertanyaan untuk halaman ini</small>
                    </div>

                    <div class="fb-q-list"></div>

                    <button type="button" class="fb-btn-add-q" onclick="addNewQuestion(this)">
                        <span>➕</span> Tambah Pertanyaan di Halaman Ini
                    </button>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', html);
        reindexPagesAndQuestions();

        // Scroll halus ke halaman baru
        const allCards = document.querySelectorAll('.fb-page-card');
        const newCard = allCards[allCards.length - 1];
        if (newCard) {
            newCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
            const titleInput = newCard.querySelector('input[name$="[title]"]');
            if (titleInput) titleInput.select();
        }
    }

    // Hapus Halaman
    function deletePageCard(btn) {
        const card = btn.closest('.fb-page-card');
        const title = card.querySelector('.page-title-preview').textContent;
        if (confirm(`Yakin ingin menghapus "${title}" beserta seluruh pertanyaan di dalamnya?`)) {
            card.remove();
            reindexPagesAndQuestions();
        }
    }

    // Tambah Pertanyaan di Dalam Halaman
    function addNewQuestion(btn) {
        const pageCard = btn.closest('.fb-page-card');
        const pIdx = pageCard.getAttribute('data-page-index');
        const qList = pageCard.querySelector('.fb-q-list');
        const fIdx = qList.querySelectorAll('.fb-q-card').length;
        const qNum = fIdx + 1;
        const fieldId = 'f_' + Math.random().toString(36).substring(2, 10);

        const html = `
            <div class="fb-q-card" data-q-index="${fIdx}">
                <input type="hidden" name="pages[${pIdx}][fields][${fIdx}][id]" value="${fieldId}">
                <input type="hidden" name="pages[${pIdx}][fields][${fIdx}][mapping]" value="">

                <div class="fb-q-header">
                    <span class="fb-q-number">Pertanyaan <span class="q-num-display">${qNum}</span></span>
                    <button type="button" onclick="deleteQuestion(this)" class="fb-btn-action fb-btn-delete" style="padding:.25rem .55rem;font-size:.78rem">
                        🗑️ Hapus Pertanyaan
                    </button>
                </div>

                <div class="fb-q-grid">
                    <div>
                        <label style="font-size:.78rem;font-weight:700;color:#64748b;display:block;margin-bottom:.3rem">Teks Pertanyaan *</label>
                        <input type="text" name="pages[${pIdx}][fields][${fIdx}][label]" required placeholder="Contoh: Nama Lengkap Siswa">
                        <input type="text" name="pages[${pIdx}][fields][${fIdx}][help]" placeholder="Petunjuk pengisian (opsional)" style="margin-top:.4rem;font-size:.82rem;padding:.4rem .65rem">
                    </div>
                    <div>
                        <label style="font-size:.78rem;font-weight:700;color:#64748b;display:block;margin-bottom:.3rem">Tipe Jawaban *</label>
                        <select name="pages[${pIdx}][fields][${fIdx}][type]" onchange="toggleOptionsBox(this)">
                            <option value="text">Jawaban Singkat (Teks)</option>
                            <option value="paragraph">Paragraf (Teks Panjang)</option>
                            <option value="number">Angka / Nomor</option>
                            <option value="date">Tanggal</option>
                            <option value="radio">Pilihan Ganda (Pilih 1)</option>
                            <option value="checkbox">Kotak Centang (Pilih Banyak)</option>
                            <option value="select">Dropdown (Pilihan Menu)</option>
                            <option value="file">Upload Berkas (File/Dokumen)</option>
                        </select>
                    </div>
                </div>

                <div class="fb-options-box" style="display:none;">
                    <label>Daftar Opsi Pilihan (Tulis 1 pilihan per baris):</label>
                    <small>Contoh:<br>Pilihan A<br>Pilihan B<br>Pilihan C</small>
                    <textarea name="pages[${pIdx}][fields][${fIdx}][options]" rows="3" placeholder="Pilihan 1&#10;Pilihan 2&#10;Pilihan 3"></textarea>
                </div>

                <div class="fb-q-footer">
                    <label style="cursor:pointer;font-size:.85rem;font-weight:700;display:inline-flex;align-items:center;gap:.45rem;color:#334155">
                        <input type="checkbox" name="pages[${pIdx}][fields][${fIdx}][required]" value="1">
                        <span>Wajib Diisi oleh Pendaftar</span>
                    </label>
                </div>
            </div>
        `;

        qList.insertAdjacentHTML('beforeend', html);
        reindexPagesAndQuestions();

        // Focus ke input label pertanyaan baru
        const allQuestions = qList.querySelectorAll('.fb-q-card');
        const newQuestion = allQuestions[allQuestions.length - 1];
        if (newQuestion) {
            const input = newQuestion.querySelector('input[name$="[label]"]');
            if (input) input.focus();
        }
    }

    // Hapus Pertanyaan
    function deleteQuestion(btn) {
        const qCard = btn.closest('.fb-q-card');
        const pageCard = btn.closest('.fb-page-card');
        qCard.remove();
        reindexPagesAndQuestions();
    }
</script>
@endsection
