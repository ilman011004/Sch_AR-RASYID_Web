@extends('layouts.admin')

@section('title', 'Pengaturan Formulir PPDB — '.$school->name)

@section('content')
<style>
    .fb { max-width: 980px; margin-inline: auto; color: #0f172a; }
    .fb [hidden] { display: none !important; }
    .fb button { font: inherit; cursor: pointer; }
    .fb-ic { flex: none; display: block; }

    /* ---------- Tombol ---------- */
    .fb-btn {
        display: inline-flex; align-items: center; justify-content: center; gap: .45rem;
        padding: .55rem 1rem; border-radius: 10px; border: 1px solid transparent;
        font-weight: 700; font-size: .86rem; line-height: 1.2; white-space: nowrap;
        transition: background .15s, border-color .15s, box-shadow .15s, transform .1s;
    }
    .fb-btn:active { transform: translateY(1px); }
    .fb-btn.dark { background: #0f172a; color: #fff; }
    .fb-btn.dark:hover { background: #1e293b; }
    .fb-btn.primary { background: var(--primary); color: #fff; box-shadow: 0 6px 16px color-mix(in srgb, var(--primary) 28%, transparent); }
    .fb-btn.primary:hover { filter: brightness(.93); }
    .fb-btn.outline { background: #fff; border-color: #e2e8f0; color: #334155; }
    .fb-btn.outline:hover { background: #f8fafc; border-color: #cbd5e1; }
    .fb-btn.soft { background: color-mix(in srgb, var(--primary) 9%, #fff); color: var(--primary); border-color: color-mix(in srgb, var(--primary) 22%, #fff); }
    .fb-btn.soft:hover { background: color-mix(in srgb, var(--primary) 16%, #fff); }
    .fb-btn.lg { padding: .75rem 1.5rem; font-size: .92rem; }

    .fb-ibtn {
        width: 32px; height: 32px; display: grid; place-items: center; flex: none;
        border: 0; border-radius: 8px; background: transparent; color: #64748b;
        transition: background .15s, color .15s;
    }
    .fb-ibtn:hover:not(:disabled) { background: #f1f5f9; color: #0f172a; }
    .fb-ibtn.danger:hover:not(:disabled) { background: #fee2e2; color: #b91c1c; }
    .fb-ibtn:disabled { opacity: .3; cursor: default; }

    /* ---------- Header ---------- */
    .fb-hero {
        display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;
        background: #fff; border: 1px solid #e2e8f0; border-radius: 16px;
        padding: 1.4rem 1.6rem; margin-bottom: 1rem;
        box-shadow: 0 2px 10px rgba(15, 23, 42, .03);
    }
    .fb-eyebrow { margin: 0 0 .2rem; font-size: .72rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: var(--primary); }
    .fb-title { margin: 0; font-size: 1.45rem; font-weight: 800; letter-spacing: -.01em; }
    .fb-desc { margin: .3rem 0 0; font-size: .88rem; color: #64748b; max-width: 520px; }
    .fb-hero-actions { display: flex; align-items: center; gap: .55rem; flex-wrap: wrap; }

    /* ---------- Ringkasan angka ---------- */
    .fb-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: .8rem; margin-bottom: 1.25rem; }
    .fb-stat {
        display: flex; align-items: center; gap: .8rem;
        background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; padding: .85rem 1.05rem;
    }
    .fb-stat-ic { width: 38px; height: 38px; border-radius: 10px; display: grid; place-items: center; flex: none; }
    .fb-stat b { display: block; font-size: 1.3rem; line-height: 1.1; font-weight: 800; }
    .fb-stat span { font-size: .76rem; color: #64748b; }
    @media (max-width: 640px) { .fb-stats { grid-template-columns: 1fr; } }

    /* ---------- Nada warna ---------- */
    .tone-blue   { background: #eff6ff; color: #1d4ed8; }
    .tone-amber  { background: #fffbeb; color: #b45309; }
    .tone-violet { background: #f5f3ff; color: #6d28d9; }
    .tone-rose   { background: #fff1f2; color: #be123c; }
    .tone-teal   { background: color-mix(in srgb, var(--primary) 12%, #fff); color: var(--primary); }

    /* ---------- Chip ---------- */
    .fb-chip {
        display: inline-flex; align-items: center; gap: .3rem;
        padding: .12rem .55rem; border-radius: 999px; font-size: .72rem; font-weight: 700;
        background: #f1f5f9; color: #475569; white-space: nowrap;
    }
    .fb-chip.req { background: #fef2f2; color: #b91c1c; }
    .fb-chip.core { background: #f0f9ff; color: #0369a1; }

    /* ---------- Kartu halaman (daftar) ---------- */
    .fb-page {
        background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; margin-bottom: .8rem;
        transition: border-color .15s, box-shadow .15s, opacity .15s;
    }
    .fb-page:hover { border-color: #cbd5e1; box-shadow: 0 6px 20px rgba(15, 23, 42, .06); }
    .fb-page.is-dragging, .fb-q.is-dragging { opacity: .4; }
    .fb-page-row { display: flex; align-items: center; gap: .85rem; padding: 1rem 1.05rem; cursor: pointer; }
    .fb-handle { border: 0; background: none; color: #94a3b8; cursor: grab !important; padding: .35rem .1rem; display: flex; }
    .fb-handle:hover { color: #475569; }
    .fb-handle:active { cursor: grabbing !important; }
    .fb-page-num {
        width: 38px; height: 38px; border-radius: 11px; flex: none; display: grid; place-items: center;
        font-weight: 800; font-size: .95rem; background: color-mix(in srgb, var(--primary) 11%, #fff); color: var(--primary);
    }
    .fb-page-meta { flex: 1; min-width: 0; }
    .fb-page-title { margin: 0; font-size: 1rem; font-weight: 800; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .fb-page-sub { margin: .12rem 0 0; font-size: .8rem; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .fb-page-tags { display: flex; flex-wrap: wrap; gap: .35rem; margin-top: .45rem; }
    .fb-page-peek { margin: .4rem 0 0; font-size: .76rem; color: #94a3b8; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .fb-tools { display: flex; align-items: center; gap: .15rem; flex: none; }
    .fb-tools .fb-btn { margin-left: .4rem; }
    @media (max-width: 720px) {
        .fb-page-row { flex-wrap: wrap; }
        .fb-tools { width: 100%; justify-content: flex-end; }
    }

    /* ---------- Keadaan kosong ---------- */
    .fb-empty {
        background: #fff; border: 2px dashed #cbd5e1; border-radius: 16px;
        padding: 3.2rem 2rem; text-align: center; margin-bottom: 1.5rem;
    }
    .fb-empty-ic { width: 64px; height: 64px; margin: 0 auto 1rem; border-radius: 18px; display: grid; place-items: center; }
    .fb-empty h3 { margin: 0 0 .4rem; font-size: 1.15rem; font-weight: 800; }
    .fb-empty p { margin: 0 auto 1.4rem; max-width: 420px; font-size: .9rem; color: #64748b; }

    /* ---------- Panel samping (drawer) ---------- */
    .fb-backdrop {
        position: fixed; inset: 0; z-index: 60; background: rgba(15, 23, 42, .45);
        opacity: 0; pointer-events: none; transition: opacity .2s;
    }
    .fb-backdrop.show { opacity: 1; pointer-events: auto; }
    .fb-drawer {
        position: fixed; top: 0; right: 0; bottom: 0; z-index: 70;
        width: min(680px, 100vw); display: none; flex-direction: column;
        background: #f8fafc; box-shadow: -24px 0 60px rgba(15, 23, 42, .2);
    }
    .fb-page.is-editing .fb-drawer { display: flex; animation: fbSlide .22s ease; }
    @keyframes fbSlide { from { transform: translateX(32px); opacity: 0; } to { transform: none; opacity: 1; } }
    .fb-dr-head {
        display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem;
        padding: 1.1rem 1.4rem; background: #fff; border-bottom: 1px solid #e2e8f0;
    }
    .fb-dr-head h2 { margin: 0; font-size: 1.1rem; font-weight: 800; }
    .fb-dr-head p { margin: .15rem 0 0; font-size: .8rem; color: #64748b; }
    .fb-dr-body { flex: 1; overflow-y: auto; padding: 1.25rem 1.4rem 2rem; }
    .fb-dr-foot {
        display: flex; align-items: center; justify-content: space-between; gap: 1rem;
        padding: .85rem 1.4rem; background: #fff; border-top: 1px solid #e2e8f0;
    }
    .fb-dr-foot small { font-size: .76rem; color: #64748b; line-height: 1.35; }

    .fb-sec { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.1rem; margin-bottom: 1.4rem; }
    .fb-lbl { display: block; margin-bottom: .3rem; font-size: .78rem; font-weight: 700; color: #475569; }
    .fb-lbl i { color: #dc2626; font-style: normal; }
    .fb-in {
        width: 100%; padding: .55rem .75rem; border: 1px solid #cbd5e1; border-radius: 9px;
        background: #fff; font-size: .92rem; color: #0f172a; line-height: 1.4;
        transition: border-color .15s, box-shadow .15s;
    }
    .fb-in:focus { outline: 0; border-color: var(--primary); box-shadow: 0 0 0 3px color-mix(in srgb, var(--primary) 18%, transparent); }
    .fb-in.has-error { border-color: #dc2626; box-shadow: 0 0 0 3px rgba(220, 38, 38, .15); }
    .fb-in.lg { font-size: 1.02rem; font-weight: 700; }
    textarea.fb-in { resize: vertical; }
    .fb-field + .fb-field { margin-top: .85rem; }

    .fb-sec-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: .7rem; }
    .fb-sec-head h4 { margin: 0; display: flex; align-items: center; gap: .5rem; font-size: .92rem; font-weight: 800; }

    /* ---------- Pertanyaan ---------- */
    .fb-qs { display: flex; flex-direction: column; gap: .55rem; }
    .fb-q { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; transition: border-color .15s, box-shadow .15s, opacity .15s; }
    .fb-q:hover { border-color: #cbd5e1; }
    .fb-q.is-open { border-color: var(--primary); box-shadow: 0 8px 22px color-mix(in srgb, var(--primary) 11%, transparent); }
    .fb-q-row { display: flex; align-items: center; gap: .6rem; padding: .6rem .7rem; cursor: pointer; }
    .fb-q-ico { width: 34px; height: 34px; border-radius: 9px; display: grid; place-items: center; flex: none; }
    .fb-q-main { flex: 1; min-width: 0; }
    .fb-q-label { font-weight: 700; font-size: .9rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .fb-q-label.is-ph { color: #94a3b8; font-style: italic; font-weight: 500; }
    .fb-q-sub { display: flex; flex-wrap: wrap; align-items: center; gap: .35rem; margin-top: .15rem; font-size: .74rem; color: #64748b; }
    .fb-chev { color: #94a3b8; transition: transform .15s; display: flex; }
    .fb-q.is-open .fb-chev { transform: rotate(180deg); }
    .fb-q-edit { display: none; padding: 1rem; border-top: 1px solid #eef2f7; background: #fcfdfe; border-radius: 0 0 12px 12px; }
    .fb-q.is-open .fb-q-edit { display: block; }
    .fb-q-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .85rem; }
    .fb-q-grid .span2 { grid-column: 1 / -1; }
    @media (max-width: 560px) { .fb-q-grid { grid-template-columns: 1fr; } }
    .fb-opts { display: none; }
    .fb-q[data-type="radio"] .fb-opts,
    .fb-q[data-type="checkbox"] .fb-opts,
    .fb-q[data-type="select"] .fb-opts { display: block; }
    .fb-opts small, .fb-hint { display: block; margin-top: .3rem; font-size: .74rem; color: #64748b; }

    /* BARU: Aturan isian (hanya tampil untuk tipe "Jawaban singkat (teks)") */
    .fb-fmt { display: none; }
    .fb-q[data-type="text"] .fb-fmt { display: block; }
    .fb-minmax { display: grid; grid-template-columns: 1fr 1fr; gap: .5rem; }

    .fb-note {
        display: flex; gap: .5rem; align-items: flex-start; padding: .6rem .75rem; border-radius: 9px;
        background: #f0f9ff; color: #075985; font-size: .78rem; line-height: 1.4; margin-bottom: .85rem;
    }
    .fb-q-empty {
        text-align: center; padding: 1.6rem 1rem; border: 2px dashed #cbd5e1; border-radius: 12px;
        background: #fff; color: #64748b; font-size: .85rem; margin-bottom: .6rem;
    }
    .fb-q-empty b { display: block; color: #0f172a; font-size: .95rem; margin-bottom: .2rem; }

    /* Judul bagian (pembatas) — tanpa saklar "wajib diisi" */
    .fb-q[data-type="section"] { border-left: 4px solid var(--primary); background: color-mix(in srgb, var(--primary) 4%, #fff); }
    .fb-q[data-type="section"] .js-hide-section { display: none; }

    /* Template siap pakai */
    .fb-tpl {
        display: flex; align-items: center; gap: .8rem; width: 100%; text-align: left;
        padding: .8rem .9rem; border: 1px solid #e2e8f0; border-radius: 10px; background: #fff;
        color: #0f172a; transition: border-color .15s, background .15s, transform .15s;
    }
    .fb-tpl:hover { border-color: var(--primary); background: color-mix(in srgb, var(--primary) 5%, #fff); transform: translateY(-1px); }
    .fb-tpl b { display: block; font-size: .88rem; font-weight: 800; }
    .fb-tpl .t { display: block; margin-top: .15rem; font-size: .76rem; font-weight: 500; color: #64748b; line-height: 1.35; }
    .fb-tpl + .fb-tpl { margin-top: .5rem; }

    /* Saklar wajib diisi */
    .fb-switch { display: inline-flex; align-items: center; gap: .6rem; cursor: pointer; font-size: .86rem; font-weight: 700; color: #334155; user-select: none; }
    .fb-switch input { position: absolute; opacity: 0; pointer-events: none; }
    .fb-switch .trk { width: 38px; height: 22px; border-radius: 999px; background: #cbd5e1; position: relative; transition: background .15s; flex: none; }
    .fb-switch .trk::after {
        content: ""; position: absolute; top: 3px; left: 3px; width: 16px; height: 16px; border-radius: 50%;
        background: #fff; box-shadow: 0 1px 3px rgba(0, 0, 0, .25); transition: transform .15s;
    }
    .fb-switch input:checked + .trk { background: var(--primary); }
    .fb-switch input:checked + .trk::after { transform: translateX(16px); }
    .fb-switch input:focus-visible + .trk { box-shadow: 0 0 0 3px color-mix(in srgb, var(--primary) 25%, transparent); }

    /* Tambah pertanyaan */
    .fb-add { margin-top: .9rem; padding: 1rem; background: #fff; border: 1px dashed color-mix(in srgb, var(--primary) 55%, #fff); border-radius: 12px; }
    .fb-add-title { margin: 0 0 .65rem; font-size: .8rem; font-weight: 800; color: #475569; display: flex; align-items: center; gap: .4rem; }
    .fb-add-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: .5rem; }
    @media (max-width: 560px) { .fb-add-grid { grid-template-columns: repeat(2, 1fr); } }
    .fb-type-btn {
        display: flex; flex-direction: column; align-items: center; gap: .35rem; padding: .7rem .4rem;
        border: 1px solid #e2e8f0; border-radius: 10px; background: #fff; color: #334155;
        font-size: .76rem; font-weight: 700; transition: all .15s;
    }
    .fb-type-btn .fb-ic-wrap { width: 30px; height: 30px; border-radius: 8px; display: grid; place-items: center; }
    .fb-type-btn:hover { border-color: var(--primary); background: color-mix(in srgb, var(--primary) 5%, #fff); transform: translateY(-1px); }

    /* ---------- Bilah simpan ---------- */
    .fb-save {
        position: sticky; bottom: 1rem; z-index: 30; margin-top: 1.5rem;
        display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;
        background: rgba(255, 255, 255, .96); backdrop-filter: blur(8px);
        border: 1px solid #e2e8f0; border-radius: 14px; padding: .85rem 1.2rem;
        box-shadow: 0 10px 30px rgba(15, 23, 42, .12);
    }
    .fb-save-state { display: flex; align-items: center; gap: .6rem; font-size: .86rem; font-weight: 700; color: #334155; }
    .fb-dot { width: 9px; height: 9px; border-radius: 50%; background: #22c55e; box-shadow: 0 0 0 4px rgba(34, 197, 94, .16); }
    .fb-save.is-dirty .fb-dot { background: #f59e0b; box-shadow: 0 0 0 4px rgba(245, 158, 11, .2); }

    /* ---------- Toast ---------- */
    .fb-toast {
        position: fixed; left: 50%; bottom: 5.5rem; z-index: 95; transform: translate(-50%, 16px);
        display: flex; align-items: center; gap: .9rem; max-width: min(92vw, 520px);
        background: #0f172a; color: #fff; padding: .7rem 1rem; border-radius: 12px; font-size: .86rem;
        box-shadow: 0 14px 36px rgba(15, 23, 42, .35); opacity: 0; pointer-events: none; transition: opacity .2s, transform .2s;
    }
    .fb-toast.show { opacity: 1; transform: translate(-50%, 0); pointer-events: auto; }
    .fb-toast button { border: 0; background: none; color: #5eead4; font-weight: 800; padding: 0; }

    /* ---------- Pratinjau ---------- */
    .fb-modal { position: fixed; inset: 0; z-index: 90; display: grid; place-items: center; padding: 1rem; background: rgba(15, 23, 42, .55); }
    .fb-modal-card { width: min(720px, 100%); max-height: calc(100vh - 2rem); display: flex; flex-direction: column; background: #fff; border-radius: 18px; overflow: hidden; box-shadow: 0 30px 80px rgba(0, 0, 0, .35); }
    .fb-modal-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; padding: 1.1rem 1.4rem; border-bottom: 1px solid #e2e8f0; }
    .fb-modal-head h2 { margin: 0; font-size: 1.1rem; font-weight: 800; }
    .fb-modal-head p { margin: .15rem 0 0; font-size: .78rem; color: #64748b; }
    .fb-pv-steps { display: flex; gap: .4rem; padding: .75rem 1.4rem; overflow-x: auto; border-bottom: 1px solid #eef2f7; background: #f8fafc; }
    .fb-pv-step { display: inline-flex; align-items: center; gap: .45rem; padding: .4rem .8rem; border-radius: 999px; border: 1px solid #e2e8f0; background: #fff; font-size: .78rem; font-weight: 700; color: #475569; white-space: nowrap; }
    .fb-pv-step span { width: 20px; height: 20px; display: grid; place-items: center; border-radius: 50%; background: #e2e8f0; font-size: .7rem; }
    .fb-pv-step.on { background: var(--primary); border-color: var(--primary); color: #fff; }
    .fb-pv-step.on span { background: rgba(255, 255, 255, .25); }
    .fb-pv-body { flex: 1; overflow-y: auto; padding: 1.4rem; }
    .fb-pv-body h3 { margin: 0; font-size: 1.15rem; font-weight: 800; }
    .fb-pv-body > p { margin: .25rem 0 1.2rem; font-size: .86rem; color: #64748b; }
    .fb-pv-field { margin-bottom: 1.05rem; }
    .fb-pv-lbl { display: block; margin-bottom: .3rem; font-size: .86rem; font-weight: 700; }
    .fb-pv-lbl i { color: #dc2626; font-style: normal; margin-left: .15rem; }
    .fb-pv-field small { display: block; margin-top: .25rem; font-size: .76rem; color: #64748b; }
    .fb-pv-field input[type="text"], .fb-pv-field input[type="number"], .fb-pv-field input[type="date"], .fb-pv-field select, .fb-pv-field textarea {
        width: 100%; padding: .55rem .75rem; border: 1px solid #cbd5e1; border-radius: 9px; background: #f8fafc; font-size: .9rem;
    }
    .fb-pv-opts { display: flex; flex-direction: column; gap: .4rem; }
    .fb-pv-opts label { display: flex; align-items: center; gap: .5rem; font-size: .88rem; }
    .fb-pv-file { display: flex; align-items: center; justify-content: center; gap: .5rem; padding: 1.1rem; border: 2px dashed #cbd5e1; border-radius: 10px; color: #64748b; font-size: .85rem; background: #f8fafc; }
    .fb-pv-none { padding: 1.4rem; text-align: center; color: #94a3b8; font-size: .86rem; border: 2px dashed #e2e8f0; border-radius: 10px; }
    .fb-modal-foot { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: .85rem 1.4rem; border-top: 1px solid #e2e8f0; }
    .fb-modal-foot small { font-size: .78rem; color: #64748b; }
</style>

<div class="fb">

    {{-- ===== Header ===== --}}
    <header class="fb-hero">
        <div>
            <p class="fb-eyebrow">Pengaturan PPDB</p>
            <h1 class="fb-title">Formulir Pendaftaran</h1>
            <p class="fb-desc">{{ $school->name }} — susun halaman dan pertanyaan yang diisi orang tua saat mendaftar.</p>
        </div>
        <div class="fb-hero-actions">
            <button type="button" class="fb-btn outline" data-act="preview"><span data-ic="eye"></span> Pratinjau</button>
            <form method="post" action="{{ route('admin.ppdb.form-builder.reset') }}" id="fbResetForm" style="margin:0">
                @csrf
                <button type="submit" class="fb-btn outline"><span data-ic="reset"></span> Reset Default</button>
            </form>
            <button type="button" class="fb-btn dark" data-act="add-page"><span data-ic="plus"></span> Tambah Halaman</button>
        </div>
    </header>

    {{-- ===== Ringkasan ===== --}}
    <div class="fb-stats">
        <div class="fb-stat"><div class="fb-stat-ic tone-teal"><span data-ic="pages" data-s="20"></span></div><div><b id="stPages">0</b><span>Halaman</span></div></div>
        <div class="fb-stat"><div class="fb-stat-ic tone-violet"><span data-ic="help" data-s="20"></span></div><div><b id="stQs">0</b><span>Total pertanyaan</span></div></div>
        <div class="fb-stat"><div class="fb-stat-ic tone-rose"><span data-ic="star" data-s="20"></span></div><div><b id="stReq">0</b><span>Wajib diisi</span></div></div>
    </div>

    <form method="post" action="{{ route('admin.ppdb.form-builder.update') }}" id="fbMainForm" novalidate>
        @csrf @method('PUT')

        {{-- Daftar halaman (ringkas). Pertanyaan ada di dalam panel "Edit Halaman". --}}
        <div id="fbPages"></div>

        <div id="fbEmpty" class="fb-empty" hidden>
            <div class="fb-empty-ic tone-teal"><span data-ic="pages" data-s="30"></span></div>
            <h3>Belum ada halaman formulir</h3>
            <p>Mulai dengan membuat halaman pertama, misalnya “Biodata Calon Siswa”. Pertanyaan ditambahkan di dalam setiap halaman.</p>
            <button type="button" class="fb-btn primary lg" data-act="add-page"><span data-ic="plus"></span> Tambah Halaman Pertama</button>
        </div>

        <div class="fb-save" id="fbSave" hidden>
            <div class="fb-save-state"><span class="fb-dot"></span><span id="fbSaveText">Belum ada perubahan</span></div>
            <div style="display:flex;gap:.6rem;align-items:center">
                <button type="button" class="fb-btn outline" data-act="add-page"><span data-ic="plus"></span> Halaman Baru</button>
                <button type="submit" class="fb-btn primary lg">Simpan Formulir</button>
            </div>
        </div>
    </form>

    <div class="fb-backdrop" id="fbBackdrop" data-act="close"></div>
    <div class="fb-toast" id="fbToast" role="status" aria-live="polite"></div>

    {{-- ===== Pratinjau ===== --}}
    <div class="fb-modal" id="fbModal" hidden>
        <div class="fb-modal-card" role="dialog" aria-modal="true" aria-label="Pratinjau formulir">
            <div class="fb-modal-head">
                <div>
                    <h2>Pratinjau Formulir</h2>
                    <p>Perkiraan tampilan bagi pendaftar. Tampilan asli di situs bisa sedikit berbeda.</p>
                </div>
                <button type="button" class="fb-ibtn" data-act="pv-close" aria-label="Tutup pratinjau"><span data-ic="x" data-s="18"></span></button>
            </div>
            <nav class="fb-pv-steps" id="fbPvSteps"></nav>
            <div class="fb-pv-body" id="fbPvBody"></div>
            <div class="fb-modal-foot">
                <button type="button" class="fb-btn outline" data-act="pv-prev">← Sebelumnya</button>
                <small id="fbPvCount"></small>
                <button type="button" class="fb-btn primary" data-act="pv-next">Berikutnya →</button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    'use strict';

    const INITIAL = @json($schema['pages'] ?? []);

    /* ---------- Referensi elemen ---------- */
    const form      = document.getElementById('fbMainForm');
    const list      = document.getElementById('fbPages');
    const emptyBox  = document.getElementById('fbEmpty');
    const saveBar   = document.getElementById('fbSave');
    const saveText  = document.getElementById('fbSaveText');
    const backdrop  = document.getElementById('fbBackdrop');
    const toastEl   = document.getElementById('fbToast');
    const modal     = document.getElementById('fbModal');

    /* ---------- Ikon ---------- */
    const ICONS = {
        grip: '<circle cx="9" cy="6" r="1.5" fill="currentColor" stroke="none"/><circle cx="15" cy="6" r="1.5" fill="currentColor" stroke="none"/><circle cx="9" cy="12" r="1.5" fill="currentColor" stroke="none"/><circle cx="15" cy="12" r="1.5" fill="currentColor" stroke="none"/><circle cx="9" cy="18" r="1.5" fill="currentColor" stroke="none"/><circle cx="15" cy="18" r="1.5" fill="currentColor" stroke="none"/>',
        edit: '<path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/>',
        copy: '<rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>',
        trash: '<path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>',
        up: '<path d="m18 15-6-6-6 6"/>',
        down: '<path d="m6 9 6 6 6-6"/>',
        plus: '<path d="M12 5v14M5 12h14"/>',
        eye: '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/>',
        lock: '<rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>',
        x: '<path d="M18 6 6 18M6 6l12 12"/>',
        reset: '<path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/>',
        pages: '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/><path d="M8 13h8M8 17h5"/>',
        help: '<circle cx="12" cy="12" r="10"/><path d="M9.1 9a3 3 0 0 1 5.8 1c0 2-3 3-3 3"/><path d="M12 17h.01"/>',
        star: '<path d="m12 2 3.1 6.3 6.9 1-5 4.9 1.2 6.8-6.2-3.3-6.2 3.3L8 14.2 3 9.3l6.9-1Z"/>',
        info: '<circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/>',
        type: '<path d="M4 7V5h16v2"/><path d="M12 5v14"/><path d="M9 19h6"/>',
        lines: '<path d="M3 6h18M3 12h18M3 18h12"/>',
        hash: '<path d="M4 9h16M4 15h16M10 3 8 21M16 3l-2 18"/>',
        calendar: '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
        radio: '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="3.5" fill="currentColor"/>',
        checksq: '<rect x="3" y="3" width="18" height="18" rx="3"/><path d="m8 12 3 3 5-6"/>',
        select: '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m9 11 3 3 3-3"/>',
        heading: '<path d="M4 12h16"/><path d="M4 5v14"/><path d="M20 5v14"/>',
        map: '<path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>',
        users: '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.9"/><path d="M16 3.1a4 4 0 0 1 0 7.8"/>',
        book: '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/>',
        heart: '<path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1-1.1a5.5 5.5 0 0 0-7.8 7.8l1 1.1L12 21l7.8-7.5 1-1.1a5.5 5.5 0 0 0 0-7.8Z"/>',
        home: '<path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2Z"/><path d="M9 22V12h6v10"/>',
        upload: '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m17 8-5-5-5 5"/><path d="M12 3v12"/>'
    };
    const ic = (name, size) => {
        size = size || 16;
        return '<svg class="fb-ic" width="' + size + '" height="' + size + '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' + (ICONS[name] || '') + '</svg>';
    };

    /* ---------- Data tipe pertanyaan ---------- */
    const TYPES = {
        text:      { label: 'Jawaban singkat (teks)',        short: 'Teks',          tone: 'blue',   icon: 'type' },
        paragraph: { label: 'Paragraf (teks panjang)',       short: 'Paragraf',      tone: 'blue',   icon: 'lines' },
        number:    { label: 'Angka / nomor',                 short: 'Angka',         tone: 'blue',   icon: 'hash' },
        date:      { label: 'Tanggal',                       short: 'Tanggal',       tone: 'amber',  icon: 'calendar' },
        radio:     { label: 'Pilihan ganda (pilih 1)',       short: 'Pilihan ganda', tone: 'violet', icon: 'radio' },
        checkbox:  { label: 'Kotak centang (pilih banyak)',  short: 'Centang',       tone: 'violet', icon: 'checksq' },
        select:    { label: 'Dropdown (menu pilihan)',       short: 'Dropdown',      tone: 'violet', icon: 'select' },
        file:      { label: 'Unggah berkas / dokumen',       short: 'Berkas',        tone: 'rose',   icon: 'upload' },
        region:    { label: 'Alamat bertingkat (Provinsi → Desa)', short: 'Alamat',  tone: 'amber',  icon: 'map' },
        section:   { label: 'Judul bagian (pembatas, tanpa jawaban)', short: 'Judul Bagian', tone: 'teal', icon: 'heading' }
    };
    const TEMPLATES = @json($templates ?? []);
    const HAS_OPTIONS = ['radio', 'checkbox', 'select'];

    // BARU: aturan isian untuk pertanyaan bertipe teks.
    const FORMATS = {
        '':       'Bebas (tanpa aturan)',
        'nik':    'NIK (tepat 16 angka)',
        'phone':  'Nomor HP (08xx…, 10–14 digit)',
        'digits': 'Angka saja (atur panjang di sebelah)',
        'email':  'Email'
    };
    // Aturan bawaan untuk pertanyaan data inti, sama dengan yang dipakai server.
    const DEFAULT_FMT = { nik: 'nik', parent_phone: 'phone', parent_email: 'email' };
    const fmtOptions = sel => Object.keys(FORMATS).map(k =>
        '<option value="' + k + '"' + (k === (sel || '') ? ' selected' : '') + '>' + FORMATS[k] + '</option>').join('');

    // Pertanyaan "data inti" terhubung ke kolom data pendaftar (lihat FormBuilderController).
    const MAPPING = {
        student_name: 'Nama siswa', nik: 'NIK', gender: 'Jenis kelamin', birth_place: 'Tempat lahir',
        birth_date: 'Tanggal lahir', religion: 'Agama', address: 'Alamat', previous_school: 'Asal sekolah',
        parent_name: 'Nama orang tua', parent_phone: 'No. HP orang tua', parent_email: 'Email orang tua',
        parent_job: 'Pekerjaan orang tua', doc_birth_cert: 'Akta kelahiran', doc_family_card: 'Kartu keluarga',
        doc_photo: 'Pas foto'
    };

    const TYPE_OPTIONS = Object.keys(TYPES).map(k => '<option value="' + k + '">' + TYPES[k].label + '</option>').join('');
    const ADD_BUTTONS = Object.keys(TYPES).map(k =>
        '<button type="button" class="fb-type-btn" data-act="add-q" data-type="' + k + '">' +
        '<span class="fb-ic-wrap tone-' + TYPES[k].tone + '">' + ic(TYPES[k].icon) + '</span><span>' + TYPES[k].short + '</span></button>'
    ).join('');

    /* ---------- Util ---------- */
    const esc = s => String(s == null ? '' : s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const newId = () => 'f_' + Math.random().toString(36).slice(2, 10);
    const val = (root, k) => { const el = root.querySelector('[data-k="' + k + '"]'); return el ? el.value : ''; };
    const pageTitleEl = pg => pg.querySelector('.fb-sec [data-k="title"]');
    const pageIndex = pg => Array.prototype.indexOf.call(list.children, pg);
    const realQs = root => Array.prototype.filter.call(root.querySelectorAll('.fb-q'), q => q.dataset.type !== 'section');

    // Tombol template siap pakai (satu tombol per template dari controller).
    const TPL_BUTTONS = Object.keys(TEMPLATES).map(k => {
        const t = TEMPLATES[k];
        return '<button type="button" class="fb-tpl" data-act="add-tpl" data-key="' + esc(k) + '">' +
            '<span class="fb-q-ico tone-teal">' + ic(t.icon || 'users', 18) + '</span>' +
            '<span><b>' + esc(t.title) + '</b><span class="t">' + esc(t.desc) + '</span></span></button>';
    }).join('');

    /* ---------- Template ---------- */
    function qHtml(d) {
        const t = TYPES[d.type] ? d.type : 'text';
        const opts = Array.isArray(d.options) ? d.options.join('\n') : (d.options || '');
        return '' +
        '<div class="fb-q" data-type="' + t + '">' +
            '<input type="hidden" data-k="id" value="' + esc(d.id || newId()) + '">' +
            '<input type="hidden" data-k="mapping" value="' + esc(d.mapping) + '">' +
            '<div class="fb-q-row" data-act="q-toggle">' +
                '<button type="button" class="fb-handle" data-handle title="Seret untuk mengubah urutan" aria-label="Seret untuk mengubah urutan">' + ic('grip') + '</button>' +
                '<span class="fb-q-ico"></span>' +
                '<div class="fb-q-main">' +
                    '<div class="fb-q-label js-qlabel"></div>' +
                    '<div class="fb-q-sub"><span class="js-qtype"></span><span class="fb-chip req js-qreq" hidden>Wajib</span><span class="fb-chip core js-qcore" hidden></span></div>' +
                '</div>' +
                '<div class="fb-tools">' +
                    '<button type="button" class="fb-ibtn" data-act="q-up" title="Naikkan" aria-label="Naikkan pertanyaan">' + ic('up') + '</button>' +
                    '<button type="button" class="fb-ibtn" data-act="q-down" title="Turunkan" aria-label="Turunkan pertanyaan">' + ic('down') + '</button>' +
                    '<button type="button" class="fb-ibtn" data-act="q-dup" title="Duplikat" aria-label="Duplikat pertanyaan">' + ic('copy') + '</button>' +
                    '<button type="button" class="fb-ibtn danger" data-act="q-del" title="Hapus" aria-label="Hapus pertanyaan">' + ic('trash') + '</button>' +
                '</div>' +
                '<span class="fb-chev">' + ic('down') + '</span>' +
            '</div>' +
            '<div class="fb-q-edit">' +
                '<div class="fb-note js-qnote" hidden>' + ic('info') + '<span></span></div>' +
                '<div class="fb-q-grid">' +
                    '<div class="span2"><label class="fb-lbl js-lbl-label">Teks pertanyaan <i>*</i></label>' +
                        '<input type="text" class="fb-in" data-k="label" value="' + esc(d.label) + '" placeholder="Contoh: Nama lengkap calon siswa"></div>' +
                    '<div><label class="fb-lbl">Tipe jawaban</label>' +
                        '<select class="fb-in" data-k="type">' + TYPE_OPTIONS.replace('value="' + t + '"', 'value="' + t + '" selected') + '</select></div>' +
                    '<div class="js-help-wrap"><label class="fb-lbl js-lbl-help">Petunjuk pengisian (opsional)</label>' +
                        '<input type="text" class="fb-in" data-k="help" value="' + esc(d.help) + '" placeholder="Contoh: Sesuai akta kelahiran"></div>' +
                    /* BARU: aturan isian, hanya tampil untuk tipe teks */
                    '<div class="fb-fmt"><label class="fb-lbl">Aturan isian</label>' +
                        '<select class="fb-in" data-k="format">' + fmtOptions(d.format || DEFAULT_FMT[d.mapping] || '') + '</select></div>' +
                    '<div class="fb-fmt"><label class="fb-lbl">Panjang karakter (min – maks)</label>' +
                        '<div class="fb-minmax">' +
                            '<input type="number" class="fb-in" data-k="min" min="0" max="255" value="' + esc(d.min) + '" placeholder="Min">' +
                            '<input type="number" class="fb-in" data-k="max" min="1" max="255" value="' + esc(d.max) + '" placeholder="Maks">' +
                        '</div></div>' +
                    '<div class="span2 fb-opts"><label class="fb-lbl">Pilihan jawaban <i>*</i></label>' +
                        '<textarea class="fb-in" data-k="options" rows="4" placeholder="Pilihan 1&#10;Pilihan 2&#10;Pilihan 3">' + esc(opts) + '</textarea>' +
                        '<small>Tulis satu pilihan per baris.</small></div>' +
                    '<div class="span2 js-hide-section"><label class="fb-switch"><input type="checkbox" data-k="required" value="1"' + (d.required ? ' checked' : '') + '><span class="trk"></span><span>Wajib diisi oleh pendaftar</span></label></div>' +
                '</div>' +
            '</div>' +
        '</div>';
    }

    function pageHtml(d) {
        const fields = (d.fields || []).map(qHtml).join('');
        return '' +
        '<div class="fb-page">' +
            '<div class="fb-page-row" data-act="edit">' +
                '<button type="button" class="fb-handle" data-handle title="Seret untuk mengubah urutan halaman" aria-label="Seret untuk mengubah urutan halaman">' + ic('grip', 18) + '</button>' +
                '<span class="fb-page-num js-num">1</span>' +
                '<div class="fb-page-meta">' +
                    '<h3 class="fb-page-title js-ptitle"></h3>' +
                    '<p class="fb-page-sub js-psub" hidden></p>' +
                    '<div class="fb-page-tags"><span class="fb-chip js-pcount"></span><span class="fb-chip req js-preq" hidden></span></div>' +
                    '<p class="fb-page-peek js-ppeek" hidden></p>' +
                '</div>' +
                '<div class="fb-tools">' +
                    '<button type="button" class="fb-ibtn" data-act="pg-up" title="Naikkan halaman" aria-label="Naikkan halaman">' + ic('up') + '</button>' +
                    '<button type="button" class="fb-ibtn" data-act="pg-down" title="Turunkan halaman" aria-label="Turunkan halaman">' + ic('down') + '</button>' +
                    '<button type="button" class="fb-ibtn" data-act="pg-dup" title="Duplikat halaman" aria-label="Duplikat halaman">' + ic('copy') + '</button>' +
                    '<button type="button" class="fb-ibtn danger" data-act="pg-del" title="Hapus halaman" aria-label="Hapus halaman">' + ic('trash') + '</button>' +
                    '<button type="button" class="fb-btn soft" data-act="edit">' + ic('edit') + ' Edit</button>' +
                '</div>' +
            '</div>' +
            '<section class="fb-drawer" role="dialog" aria-modal="true" aria-label="Edit halaman">' +
                '<header class="fb-dr-head">' +
                    '<div><h2 class="js-dtitle">Edit Halaman</h2><p>Atur judul halaman dan pertanyaan di dalamnya.</p></div>' +
                    '<button type="button" class="fb-ibtn" data-act="close" aria-label="Tutup panel">' + ic('x', 18) + '</button>' +
                '</header>' +
                '<div class="fb-dr-body">' +
                    '<div class="fb-sec">' +
                        '<div class="fb-field"><label class="fb-lbl">Judul halaman <i>*</i></label>' +
                            '<input type="text" class="fb-in lg" data-k="title" value="' + esc(d.title) + '" placeholder="Contoh: Biodata Calon Siswa"></div>' +
                        '<div class="fb-field"><label class="fb-lbl">Petunjuk halaman (opsional)</label>' +
                            '<input type="text" class="fb-in" data-k="subtitle" value="' + esc(d.subtitle) + '" placeholder="Contoh: Lengkapi data sesuai kartu keluarga"></div>' +
                    '</div>' +
                    '<div class="fb-sec-head"><h4>Pertanyaan <span class="fb-chip js-dcount">0</span></h4></div>' +
                    '<div class="fb-qs" data-qs>' + fields + '</div>' +
                    '<div class="fb-q-empty js-qempty" hidden><b>Belum ada pertanyaan</b>Pilih jenis pertanyaan di bawah untuk menambahkannya ke halaman ini.</div>' +
                    '<div class="fb-add"><p class="fb-add-title">' + ic('plus', 14) + ' Tambah pertanyaan</p><div class="fb-add-grid">' + ADD_BUTTONS + '</div></div>' +
                    (TPL_BUTTONS ? '<div class="fb-add"><p class="fb-add-title">' + ic('users', 14) + ' Template siap pakai</p>' + TPL_BUTTONS + '</div>' : '') +
                '</div>' +
                '<footer class="fb-dr-foot">' +
                    '<small>Perubahan baru tersimpan permanen setelah Anda menekan “Simpan Formulir”.</small>' +
                    '<button type="button" class="fb-btn primary" data-act="close">Selesai</button>' +
                '</footer>' +
            '</section>' +
        '</div>';
    }

    /* ---------- Penyegaran tampilan ---------- */
    function refreshQ(q) {
        const t = TYPES[val(q, 'type')] ? val(q, 'type') : 'text';
        const T = TYPES[t];
        q.dataset.type = t;
        const label = val(q, 'label').trim();
        const L = q.querySelector('.js-qlabel');
        L.textContent = label || (t === 'section' ? 'Judul bagian belum diisi' : 'Pertanyaan belum diberi teks');
        L.classList.toggle('is-ph', !label);
        q.querySelector('.js-qtype').textContent = T.short;
        const ico = q.querySelector('.fb-q-ico');
        if (ico.dataset.t !== t) { ico.dataset.t = t; ico.className = 'fb-q-ico tone-' + T.tone; ico.innerHTML = ic(T.icon); }
        const isSec = t === 'section';
        if (isSec) { q.querySelector('[data-k="required"]').checked = false; }
        q.querySelector('.js-lbl-label').innerHTML = isSec ? 'Judul bagian <i>*</i>' : 'Teks pertanyaan <i>*</i>';
        q.querySelector('.js-lbl-help').textContent = isSec ? 'Keterangan bagian (opsional)' : 'Petunjuk pengisian (opsional)';
        q.querySelector('.js-qreq').hidden = isSec || !q.querySelector('[data-k="required"]').checked;
        const m = val(q, 'mapping');
        const core = q.querySelector('.js-qcore');
        core.hidden = !m;
        if (m) { core.innerHTML = ic('lock', 11) + ' Data inti'; }
        const note = q.querySelector('.js-qnote');
        note.hidden = !m;
        if (m) { note.querySelector('span').textContent = 'Pertanyaan ini terhubung ke data pendaftar (' + (MAPPING[m] || m) + '). Teks dan petunjuk boleh diubah; sebaiknya jangan dihapus agar data tersebut tetap terkumpul.'; }
    }

    function refreshPage(pg) {
        const qs = realQs(pg);
        const p = pageIndex(pg);
        const title = pageTitleEl(pg).value.trim();
        const sub = pg.querySelector('.fb-sec [data-k="subtitle"]').value.trim();
        pg.querySelector('.js-num').textContent = p + 1;
        pg.querySelector('.js-ptitle').textContent = title || 'Halaman tanpa judul';
        const s = pg.querySelector('.js-psub'); s.textContent = sub; s.hidden = !sub;
        const req = Array.prototype.filter.call(qs, q => q.querySelector('[data-k="required"]').checked).length;
        pg.querySelector('.js-pcount').textContent = qs.length + ' pertanyaan';
        const r = pg.querySelector('.js-preq'); r.textContent = req + ' wajib diisi'; r.hidden = qs.length === 0;
        const labels = Array.prototype.map.call(qs, q => val(q, 'label').trim()).filter(Boolean);
        const secs = pg.querySelectorAll('.fb-q[data-type="section"]').length;
        if (secs) { pg.querySelector('.js-pcount').textContent += ' · ' + secs + ' bagian'; }
        const peek = pg.querySelector('.js-ppeek');
        peek.hidden = labels.length === 0;
        peek.textContent = labels.slice(0, 3).join('  ·  ') + (labels.length > 3 ? '  ·  +' + (labels.length - 3) + ' lainnya' : '');
        pg.querySelector('.js-dtitle').textContent = 'Edit Halaman ' + (p + 1);
        pg.querySelector('.js-dcount').textContent = qs.length;
        pg.querySelector('.js-qempty').hidden = pg.querySelectorAll('.fb-q').length > 0;
    }

    function updateStats() {
        const qs = realQs(list);
        document.getElementById('stPages').textContent = list.children.length;
        document.getElementById('stQs').textContent = qs.length;
        document.getElementById('stReq').textContent = Array.prototype.filter.call(qs, q => q.querySelector('[data-k="required"]').checked).length;
    }

    // Menyusun ulang nama input (pages[i][fields][j][...]) sesuai urutan di layar.
    function reindex(markDirty) {
        const pages = Array.prototype.slice.call(list.children);
        pages.forEach((pg, p) => {
            pg.querySelectorAll('.fb-sec [data-k]').forEach(el => { el.name = 'pages[' + p + '][' + el.dataset.k + ']'; });
            const qs = pg.querySelectorAll('.fb-q');
            qs.forEach((q, i) => {
                q.querySelectorAll('[data-k]').forEach(el => { el.name = 'pages[' + p + '][fields][' + i + '][' + el.dataset.k + ']'; });
                q.querySelector('[data-act="q-up"]').disabled = i === 0;
                q.querySelector('[data-act="q-down"]').disabled = i === qs.length - 1;
                refreshQ(q);
            });
            pg.querySelector('[data-act="pg-up"]').disabled = p === 0;
            pg.querySelector('[data-act="pg-down"]').disabled = p === pages.length - 1;
            refreshPage(pg);
        });
        emptyBox.hidden = pages.length > 0;
        saveBar.hidden = pages.length === 0;
        updateStats();
        if (markDirty !== false) setDirty(true);
    }

    /* ---------- Status simpan ---------- */
    let dirty = false;
    function setDirty(v) {
        dirty = v;
        saveBar.classList.toggle('is-dirty', v);
        saveText.textContent = v ? 'Ada perubahan yang belum disimpan' : 'Semua perubahan sudah tersimpan';
    }
    window.addEventListener('beforeunload', e => { if (dirty) { e.preventDefault(); e.returnValue = ''; } });

    /* ---------- Toast ---------- */
    let toastTimer;
    function hideToast() { toastEl.classList.remove('show'); }
    function toast(msg, actionLabel, action) {
        toastEl.innerHTML = '';
        const s = document.createElement('span'); s.textContent = msg; toastEl.appendChild(s);
        if (actionLabel) {
            const b = document.createElement('button'); b.type = 'button'; b.textContent = actionLabel;
            b.addEventListener('click', () => { action(); hideToast(); });
            toastEl.appendChild(b);
        }
        toastEl.classList.add('show');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(hideToast, actionLabel ? 7000 : 4000);
    }

    /* ---------- Panel edit halaman ---------- */
    function openPage(pg, selectTitle) {
        closeDrawer();
        pg.classList.add('is-editing');
        backdrop.classList.add('show');
        document.body.style.overflow = 'hidden';
        if (selectTitle) { const t = pageTitleEl(pg); setTimeout(() => { t.focus(); t.select(); }, 60); }
    }
    function closeDrawer() {
        const cur = list.querySelector('.fb-page.is-editing');
        if (cur) cur.classList.remove('is-editing');
        backdrop.classList.remove('show');
        document.body.style.overflow = '';
    }

    function openQ(q, scroll) {
        q.parentElement.querySelectorAll(':scope > .fb-q.is-open').forEach(o => { if (o !== q) o.classList.remove('is-open'); });
        q.classList.add('is-open');
        if (scroll) setTimeout(() => q.scrollIntoView({ behavior: 'smooth', block: 'center' }), 30);
    }
    function toggleQ(q) {
        if (q.classList.contains('is-open')) { q.classList.remove('is-open'); } else { openQ(q); }
    }

    /* ---------- Operasi data ---------- */
    // BARU: ikut membaca format, min, dan max agar duplikat dan pratinjau membawa aturan isian.
    function readQ(q) {
        return {
            id: val(q, 'id'), mapping: val(q, 'mapping'), label: val(q, 'label'), help: val(q, 'help'),
            type: val(q, 'type'), required: q.querySelector('[data-k="required"]').checked, options: val(q, 'options'),
            format: val(q, 'format'), min: val(q, 'min'), max: val(q, 'max')
        };
    }
    function readPage(pg) {
        return {
            title: pageTitleEl(pg).value,
            subtitle: pg.querySelector('.fb-sec [data-k="subtitle"]').value,
            fields: Array.prototype.map.call(pg.querySelectorAll('.fb-q'), readQ)
        };
    }

    function addPage() {
        list.insertAdjacentHTML('beforeend', pageHtml({ title: 'Halaman ' + (list.children.length + 1), subtitle: '', fields: [] }));
        const pg = list.lastElementChild;
        reindex();
        openPage(pg, true);
    }

    function duplicatePage(pg) {
        const d = readPage(pg);
        d.title = (d.title.trim() || 'Halaman') + ' (salinan)';
        d.fields = d.fields.map(f => Object.assign({}, f, { id: newId(), mapping: '' }));
        pg.insertAdjacentHTML('afterend', pageHtml(d));
        reindex();
        toast('Halaman diduplikat. Pertanyaan data inti pada salinan menjadi pertanyaan biasa.');
    }

    function addQuestion(pg, type) {
        const qs = pg.querySelector('[data-qs]');
        qs.insertAdjacentHTML('beforeend', qHtml({
            id: newId(), mapping: '', label: '', help: '', type: type, required: false,
            options: HAS_OPTIONS.indexOf(type) > -1 ? 'Pilihan 1\nPilihan 2' : ''
        }));
        const q = qs.lastElementChild;
        reindex();
        openQ(q, true);
        setTimeout(() => q.querySelector('[data-k="label"]').focus(), 120);
    }

    function addTemplate(pg, key) {
        const tpl = TEMPLATES[key];
        if (!tpl) { return; }
        // Hindari dua pertanyaan yang terhubung ke kolom data yang sama (mis. dua "Nama orang tua").
        const used = {};
        list.querySelectorAll('[data-k="mapping"]').forEach(i => { if (i.value) { used[i.value] = true; } });
        const qs = pg.querySelector('[data-qs]');
        const rows = (tpl.fields || []).map(f => {
            const f2 = Object.assign({}, f, { id: newId() });
            if (f2.mapping && used[f2.mapping]) { f2.mapping = ''; } else if (f2.mapping) { used[f2.mapping] = true; }
            return qHtml(f2);
        }).join('');
        qs.insertAdjacentHTML('beforeend', rows);
        reindex();
        toast('Template "' + tpl.title + '" ditambahkan. Silakan sesuaikan isinya.');
        qs.lastElementChild.scrollIntoView({ behavior: 'smooth', block: 'end' });
    }

    function duplicateQuestion(q) {
        const d = readQ(q);
        d.id = newId(); d.mapping = ''; d.label = (d.label.trim() || 'Pertanyaan') + ' (salinan)';
        q.insertAdjacentHTML('afterend', qHtml(d));
        const copy = q.nextElementSibling;
        reindex();
        openQ(copy, true);
    }

    function move(el, dir) {
        const sib = dir < 0 ? el.previousElementSibling : el.nextElementSibling;
        if (!sib) return;
        if (dir < 0) { el.parentElement.insertBefore(el, sib); } else { el.parentElement.insertBefore(sib, el); }
        reindex();
        el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    // Hapus dengan opsi "Urungkan"
    function softDelete(el, label) {
        const parent = el.parentElement, next = el.nextElementSibling;
        el.remove();
        reindex();
        toast('“' + label + '” dihapus', 'Urungkan', () => {
            parent.insertBefore(el, next && next.parentElement === parent ? next : null);
            reindex();
        });
    }

    function deleteQuestion(q) {
        const label = val(q, 'label').trim() || 'Pertanyaan';
        const m = val(q, 'mapping');
        if (m && !confirm('Pertanyaan “' + label + '” adalah data inti (' + (MAPPING[m] || m) + ') yang terhubung ke data pendaftar. Jika dihapus, data ini tidak akan terkumpul lagi.\n\nTetap hapus?')) { return; }
        softDelete(q, label);
    }

    function deletePage(pg) {
        const title = pageTitleEl(pg).value.trim() || 'Halaman';
        const core = Array.prototype.filter.call(pg.querySelectorAll('.fb-q'), q => val(q, 'mapping')).length;
        if (core && !confirm('Halaman “' + title + '” berisi ' + core + ' pertanyaan data inti yang terhubung ke data pendaftar. Jika dihapus, data tersebut tidak akan terkumpul lagi.\n\nTetap hapus halaman?')) { return; }
        softDelete(pg, title);
    }

    /* ---------- Validasi sebelum simpan ---------- */
    function validate() {
        const pages = Array.prototype.slice.call(list.children);
        for (let i = 0; i < pages.length; i++) {
            const pg = pages[i];
            const t = pageTitleEl(pg);
            if (!t.value.trim()) { return { pg: pg, el: t, msg: 'Halaman ' + (i + 1) + ' belum diberi judul.' }; }
            const qs = pg.querySelectorAll('.fb-q');
            for (let j = 0; j < qs.length; j++) {
                const q = qs[j];
                const l = q.querySelector('[data-k="label"]');
                if (!l.value.trim()) { return { pg: pg, q: q, el: l, msg: 'Halaman ' + (i + 1) + ': ada pertanyaan yang belum diberi teks.' }; }
                if (HAS_OPTIONS.indexOf(val(q, 'type')) > -1) {
                    const o = q.querySelector('[data-k="options"]');
                    if (!o.value.split(/\r?\n/).some(s => s.trim())) { return { pg: pg, q: q, el: o, msg: 'Halaman ' + (i + 1) + ': isi minimal satu pilihan jawaban.' }; }
                }
                // BARU: panjang minimal tidak boleh lebih besar dari maksimal.
                if (val(q, 'type') === 'text') {
                    const mn = parseInt(val(q, 'min'), 10), mx = parseInt(val(q, 'max'), 10);
                    if (mn > 0 && mx > 0 && mn > mx) {
                        return { pg: pg, q: q, el: q.querySelector('[data-k="min"]'), msg: 'Halaman ' + (i + 1) + ': panjang minimal tidak boleh lebih besar dari maksimal.' };
                    }
                }
            }
        }
        return null;
    }

    form.addEventListener('submit', e => {
        const err = validate();
        if (err) {
            e.preventDefault();
            openPage(err.pg);
            if (err.q) { openQ(err.q, true); }
            err.el.classList.add('has-error');
            setTimeout(() => err.el.focus(), 120);
            toast(err.msg);
            return;
        }
        reindex(false);
        dirty = false;
    });

    document.getElementById('fbResetForm').addEventListener('submit', e => {
        if (!confirm('Kembalikan susunan formulir ke default? Susunan yang sekarang akan hilang.')) { e.preventDefault(); return; }
        dirty = false;
    });

    // Enter di kolom teks tidak boleh langsung menyimpan formulir.
    form.addEventListener('keydown', e => {
        if (e.key === 'Enter' && e.target.tagName === 'INPUT' && e.target.type === 'text') { e.preventDefault(); }
    });

    /* ---------- Pratinjau ---------- */
    const pv = { i: 0, pages: [] };

    function fieldPreview(f) {
        if (f.type === 'section') {
            return '<div style="margin:1.4rem 0 1rem;padding:.7rem 1rem;border-left:4px solid var(--primary);background:color-mix(in srgb,var(--primary) 7%,#fff);border-radius:0 10px 10px 0"><b style="font-size:1rem">' + esc(f.label.trim() || '(judul bagian)') + '</b>' + (f.help ? '<div style="font-size:.8rem;color:#475569;margin-top:.2rem">' + esc(f.help) + '</div>' : '') + '</div>';
        }
        const opts = String(f.options || '').split(/\r?\n/).map(s => s.trim()).filter(Boolean);
        let ctl;
        switch (f.type) {
            case 'paragraph': ctl = '<textarea disabled rows="3"></textarea>'; break;
            case 'number': ctl = '<input type="number" disabled placeholder="0">'; break;
            case 'date': ctl = '<input type="date" disabled>'; break;
            case 'select': ctl = '<select disabled><option>Pilih salah satu…</option>' + opts.map(o => '<option>' + esc(o) + '</option>').join('') + '</select>'; break;
            case 'radio':
            case 'checkbox': ctl = '<div class="fb-pv-opts">' + opts.map(o => '<label><input type="' + f.type + '" disabled> ' + esc(o) + '</label>').join('') + '</div>'; break;
            case 'region': ctl = '<div style="display:grid;grid-template-columns:1fr 1fr;gap:.6rem"><select disabled><option>Provinsi</option></select><select disabled><option>Kabupaten / Kota</option></select><select disabled><option>Kecamatan</option></select><select disabled><option>Desa / Kelurahan</option></select></div><input type="text" disabled placeholder="Nama jalan, RT/RW, nomor rumah" style="margin-top:.6rem">'; break;
            case 'file': ctl = '<div class="fb-pv-file">' + ic('upload', 18) + ' Pilih berkas dari perangkat</div>'; break;
            default: {
                // BARU: placeholder mengikuti aturan isian yang dipilih.
                const ph = { nik: '16 digit angka', phone: 'Contoh: 081234567890', digits: 'Hanya angka', email: 'nama@email.com' }[f.format] || '';
                ctl = '<input type="text" disabled' + (ph ? ' placeholder="' + esc(ph) + '"' : '') + '>';
            }
        }
        // BARU: ringkasan aturan di bawah pertanyaan teks.
        let rule = '';
        if (f.type === 'text') {
            const parts = [];
            if (f.format === 'nik') { parts.push('Harus 16 angka'); }
            else if (f.format === 'phone') { parts.push('Nomor HP Indonesia 10–14 digit'); }
            else if (f.format === 'digits') { parts.push('Hanya angka'); }
            else if (f.format === 'email') { parts.push('Format email'); }
            if (f.format !== 'nik') {
                if (parseInt(f.min, 10) > 0) { parts.push('min. ' + parseInt(f.min, 10) + ' karakter'); }
                if (parseInt(f.max, 10) > 0) { parts.push('maks. ' + parseInt(f.max, 10) + ' karakter'); }
            }
            if (parts.length) { rule = '<small>' + esc(parts.join(' · ')) + '</small>'; }
        }
        return '<div class="fb-pv-field"><label class="fb-pv-lbl">' + esc(f.label.trim() || '(pertanyaan belum diberi teks)') +
            (f.required ? '<i>*</i>' : '') + '</label>' + ctl + (f.help ? '<small>' + esc(f.help) + '</small>' : '') + rule + '</div>';
    }

    function renderPreview() {
        const P = pv.pages, cur = P[pv.i];
        document.getElementById('fbPvSteps').innerHTML = P.map((p, i) =>
            '<button type="button" class="fb-pv-step' + (i === pv.i ? ' on' : '') + '" data-act="pv-step" data-i="' + i + '"><span>' + (i + 1) + '</span>' + esc(p.title.trim() || 'Halaman ' + (i + 1)) + '</button>'
        ).join('');
        document.getElementById('fbPvBody').innerHTML =
            '<h3>' + esc(cur.title.trim() || 'Halaman ' + (pv.i + 1)) + '</h3>' +
            (cur.subtitle.trim() ? '<p>' + esc(cur.subtitle) + '</p>' : '<p></p>') +
            (cur.fields.length ? cur.fields.map(fieldPreview).join('') : '<div class="fb-pv-none">Halaman ini belum memiliki pertanyaan.</div>');
        document.getElementById('fbPvBody').scrollTop = 0;
        document.getElementById('fbPvCount').textContent = 'Halaman ' + (pv.i + 1) + ' dari ' + P.length;
        modal.querySelector('[data-act="pv-prev"]').disabled = pv.i === 0;
        modal.querySelector('[data-act="pv-next"]').disabled = pv.i === P.length - 1;
        modal.querySelectorAll('.fb-modal-foot .fb-btn').forEach(b => { b.style.opacity = b.disabled ? '.4' : ''; });
    }
    function openPreview() {
        pv.pages = Array.prototype.map.call(list.children, readPage);
        if (!pv.pages.length) { toast('Belum ada halaman untuk dipratinjau.'); return; }
        pv.i = 0;
        renderPreview();
        modal.hidden = false;
    }
    function closePreview() { modal.hidden = true; }
    modal.addEventListener('click', e => { if (e.target === modal) { closePreview(); } });

    /* ---------- Penanganan klik (delegasi) ---------- */
    document.addEventListener('click', e => {
        if (e.target.closest('[data-handle]')) { return; }
        const el = e.target.closest('[data-act]');
        if (!el) { return; }
        const act = el.dataset.act;
        const pg = el.closest('.fb-page');
        const q = el.closest('.fb-q');

        switch (act) {
            case 'add-page': addPage(); break;
            case 'edit': openPage(pg); break;
            case 'close': closeDrawer(); break;
            case 'pg-up': move(pg, -1); break;
            case 'pg-down': move(pg, 1); break;
            case 'pg-dup': duplicatePage(pg); break;
            case 'pg-del': deletePage(pg); break;
            case 'q-toggle': toggleQ(q); break;
            case 'q-up': move(q, -1); break;
            case 'q-down': move(q, 1); break;
            case 'q-dup': duplicateQuestion(q); break;
            case 'q-del': deleteQuestion(q); break;
            case 'add-q': addQuestion(pg, el.dataset.type); break;
            case 'add-tpl': addTemplate(pg, el.dataset.key); break;
            case 'preview': openPreview(); break;
            case 'pv-close': closePreview(); break;
            case 'pv-prev': if (pv.i > 0) { pv.i--; renderPreview(); } break;
            case 'pv-next': if (pv.i < pv.pages.length - 1) { pv.i++; renderPreview(); } break;
            case 'pv-step': pv.i = parseInt(el.dataset.i, 10) || 0; renderPreview(); break;
        }
    });

    document.addEventListener('keydown', e => {
        if (e.key !== 'Escape') { return; }
        if (!modal.hidden) { closePreview(); } else { closeDrawer(); }
    });

    // Perubahan isian: segarkan tampilan ringkas dan tandai belum disimpan.
    function onEdit(e) {
        const t = e.target;
        if (!t.dataset || !t.dataset.k) { return; }
        t.classList.remove('has-error');
        const pg = t.closest('.fb-page');
        const q = t.closest('.fb-q');
        if (q) { refreshQ(q); }
        if (pg) { refreshPage(pg); }
        updateStats();
        setDirty(true);
    }
    form.addEventListener('input', onEdit);
    form.addEventListener('change', onEdit);

    /* ---------- Seret & lepas untuk mengubah urutan ---------- */
    let drag = null;
    const DRAG_SEL = '.fb-q, .fb-page';

    document.addEventListener('mousedown', e => {
        const h = e.target.closest('[data-handle]');
        if (!h) { return; }
        const item = h.closest(DRAG_SEL);
        if (item) { item.draggable = true; }
    });
    function clearDraggable() {
        document.querySelectorAll('.fb-q[draggable="true"], .fb-page[draggable="true"]').forEach(i => { i.draggable = false; });
    }
    document.addEventListener('mouseup', clearDraggable);

    document.addEventListener('dragstart', e => {
        const el = e.target;
        if (!el.matches || !el.matches(DRAG_SEL)) { return; }
        drag = { el: el, parent: el.parentElement, sel: el.classList.contains('fb-q') ? '.fb-q' : '.fb-page' };
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', 'fb');
        setTimeout(() => { if (drag) { drag.el.classList.add('is-dragging'); } }, 0);
    });
    document.addEventListener('dragover', e => {
        if (!drag) { return; }
        const over = e.target.closest ? e.target.closest(drag.sel) : null;
        if (!over || over === drag.el || over.parentElement !== drag.parent) {
            if (drag.parent.contains(e.target)) { e.preventDefault(); }
            return;
        }
        e.preventDefault();
        const r = over.getBoundingClientRect();
        const ref = e.clientY > r.top + r.height / 2 ? over.nextElementSibling : over;
        if (ref === drag.el || ref === drag.el.nextElementSibling) { return; }
        drag.parent.insertBefore(drag.el, ref);
    });
    document.addEventListener('drop', e => { if (drag) { e.preventDefault(); } });
    document.addEventListener('dragend', () => {
        if (!drag) { return; }
        drag.el.classList.remove('is-dragging');
        drag = null;
        clearDraggable();
        reindex();
    });

    /* ---------- Mulai ---------- */
    document.querySelectorAll('[data-ic]').forEach(el => { el.innerHTML = ic(el.dataset.ic, parseInt(el.dataset.s, 10) || 16); });
    list.innerHTML = INITIAL.map(pageHtml).join('');
    reindex(false);
    setDirty(false);
    saveText.textContent = 'Belum ada perubahan';
})();
</script>
@endsection