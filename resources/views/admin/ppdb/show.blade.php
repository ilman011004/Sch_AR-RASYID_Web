@extends('layouts.admin')

@section('title', 'Detail Pendaftar')

@section('content')
@php
    $done = $r->stage >= 4;
    $waLink = $r->waLink();

    /*
     * Satu daftar isian untuk ditampilkan.
     * - Pendaftar baru: dari custom_fields (formulir yang disusun admin).
     * - Pendaftar lama (belum punya custom_fields): dari kolom data lama.
     */
    $items = [];
    if (! empty($r->custom_fields) && is_array($r->custom_fields)) {
        foreach (array_values($r->custom_fields) as $i => $cf) {
            $type = $cf['type'] ?? 'text';
            $value = $cf['value'] ?? null;
            $items[] = [
                'section' => $cf['section'] ?? null,
                'label'   => $cf['label'] ?? 'Pertanyaan',
                'type'    => $type,
                'value'   => $value,
                'url'     => ($type === 'file' && filled($value)) ? route('admin.ppdb.document', [$r->id, 'custom_'.$i]) : null,
            ];
        }
    } else {
        $legacy = [
            ['Data calon siswa', 'Nama', $r->student_name],
            ['Data calon siswa', 'NIK', $r->nik],
            ['Data calon siswa', 'Jenis kelamin', $r->gender === 'L' ? 'Laki-laki' : 'Perempuan'],
            ['Data calon siswa', 'Tempat, tgl lahir', $r->birth_place.', '.optional($r->birth_date)->format('d/m/Y')],
            ['Data calon siswa', 'Agama', $r->religion],
            ['Data calon siswa', 'Alamat', $r->address],
            ['Data calon siswa', 'Asal sekolah', $r->previous_school],
            ['Orang tua / wali', 'Nama', $r->parent_name],
            ['Orang tua / wali', 'No. HP', $r->parent_phone],
            ['Orang tua / wali', 'Email', $r->parent_email],
            ['Orang tua / wali', 'Pekerjaan', $r->parent_job],
        ];
        foreach ($legacy as [$sec, $label, $value]) {
            $items[] = ['section' => $sec, 'label' => $label, 'type' => 'text', 'value' => $value, 'url' => null];
        }
        foreach ($docs as $field => $label) {
            $items[] = [
                'section' => 'Berkas',
                'label'   => $label,
                'type'    => 'file',
                'value'   => $r->{$field},
                'url'     => $r->{$field} ? route('admin.ppdb.document', [$r->id, $field]) : null,
            ];
        }
    }
@endphp

<style>
    .pd-top {
        display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: .8rem 1.4rem;
        background: #fff; border: 1px solid #e2e8f0; border-radius: 14px;
        padding: .85rem 1.2rem; margin-bottom: 1rem;
    }
    .pd-meta { display: flex; flex-wrap: wrap; gap: .4rem 1.6rem; font-size: .84rem; color: #475569; }
    .pd-meta b { display: block; font-size: .68rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: #94a3b8; }
    .pd-meta span { font-weight: 600; color: #0f172a; }
    .pd-status { display: flex; align-items: center; gap: .6rem; flex-wrap: wrap; }
    .pd-wa details { margin-top: .6rem; }

    .pd-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 1.2rem 1.3rem; margin-bottom: 1rem; }
    .pd-card > h2 { margin: 0 0 .9rem; font-size: 1.05rem; font-weight: 800; }

    .pd-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .85rem 1.6rem; }
    .pd-sec {
        grid-column: 1 / -1; margin-top: .4rem; padding: .45rem .8rem;
        border-left: 4px solid var(--primary); border-radius: 0 8px 8px 0;
        background: color-mix(in srgb, var(--primary) 6%, #fff);
        font-size: .86rem; font-weight: 800; color: var(--primary);
    }
    .pd-sec:first-child { margin-top: 0; }
    .pd-item { min-width: 0; padding-bottom: .55rem; border-bottom: 1px dashed #e8edf3; }
    .pd-item.wide { grid-column: 1 / -1; }
    .pd-lbl { display: block; font-size: .72rem; font-weight: 700; color: #94a3b8; margin-bottom: .1rem; }
    .pd-val { font-size: .92rem; font-weight: 600; color: #0f172a; word-break: break-word; }
    .pd-val .muted { color: #94a3b8; font-weight: 500; }
    .pd-file {
        display: inline-flex; align-items: center; gap: .35rem; padding: .2rem .65rem; border-radius: 999px;
        font-size: .8rem; font-weight: 700; text-decoration: none;
        background: color-mix(in srgb, var(--primary) 10%, #fff); color: var(--primary);
    }
    .pd-file:hover { background: color-mix(in srgb, var(--primary) 18%, #fff); }
    .pd-nofile { font-size: .78rem; font-weight: 700; color: #b91c1c; background: #fef2f2; padding: .15rem .6rem; border-radius: 999px; }
    @media (max-width: 640px) { .pd-grid { grid-template-columns: 1fr; } }

    /* Tahapan (accordion) */
    .pd-stage { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; margin-bottom: .8rem; overflow: hidden; }
    .pd-stage.is-current { border-color: var(--primary); box-shadow: 0 6px 18px color-mix(in srgb, var(--primary) 12%, transparent); }
    .pd-stage > summary {
        display: flex; align-items: center; justify-content: space-between; gap: .8rem;
        padding: .85rem 1.2rem; cursor: pointer; list-style: none; font-weight: 800; font-size: .95rem;
    }
    .pd-stage > summary::-webkit-details-marker { display: none; }
    .pd-stage > summary::after { content: "▾"; color: #94a3b8; transition: transform .15s; }
    .pd-stage[open] > summary::after { transform: rotate(180deg); }
    .pd-stage-tag { font-size: .68rem; font-weight: 800; padding: .1rem .55rem; border-radius: 999px; background: color-mix(in srgb, var(--primary) 12%, #fff); color: var(--primary); margin-left: .5rem; }
    .pd-stage-body { padding: .2rem 1.2rem 1.2rem; border-top: 1px solid #eef2f7; }
    .pd-stage-body h3 { font-size: .92rem; margin: 1rem 0 .6rem; }
    .pd-stage-body hr { margin: 1.1rem 0; border: 0; border-top: 1px solid #eef2f7; }
    .pd-danger { margin-top: 1.2rem; text-align: right; }
</style>

<div class="page-head">
    <h1>{{ $r->student_name }} <small>{{ $r->reg_number }} · {{ $r->school->name }}</small></h1>
    <div style="display:flex;gap:.8rem;align-items:center;flex-wrap:wrap">
        <a class="btn btn-sm btn-outline" href="{{ route('ppdb.print', [$r->school, $r->reg_number]) }}" target="_blank" style="padding:.45rem .95rem;font-weight:700">
            📄 Cetak Formulir
        </a>
        <a class="btn btn-sm" href="{{ route('ppdb.card', [$r->school, $r->reg_number]) }}" target="_blank" style="padding:.45rem .95rem;font-weight:700">
            📇 Kartu Ujian Seleksi
        </a>
        <a href="{{ auth()->user()->isSuperAdmin() ? route('admin.ppdb.all') : route('admin.ppdb.index') }}">← Kembali ke daftar</a>
    </div>
</div>

{{-- Indikator tahap --}}
<ol class="stepper">
    @foreach ($stages as $n => $label)
        <li @class(['done' => $r->stage > $n || ($done && $n === 4), 'current' => $r->stage === $n && ! $done])>
            <span class="dot">{{ ($r->stage > $n || ($done && $n === 4)) ? '✓' : $n }}</span>
            <span class="lbl">{{ $label }}</span>
        </li>
    @endforeach
</ol>

{{-- Bar ringkas: status, info pendaftaran, WhatsApp --}}
<div class="pd-top">
    <div class="pd-meta">
        <div><b>Tanggal daftar</b><span>{{ $r->created_at->format('d/m/Y H:i') }}</span></div>
        <div><b>Akun pendaftar</b><span>{{ $r->user?->email ?: '-' }}</span></div>
    </div>
    <div class="pd-status">
        <span>Status: <span class="badge s-{{ $r->status }}">{{ $r->statusLabel() }}</span></span>
        @if ($waLink)
            <a class="btn btn-sm" href="{{ $waLink }}" target="_blank" rel="noopener">Kirim WhatsApp sesuai tahap</a>
        @endif
    </div>
    @if ($waLink)
        <div class="pd-wa" style="flex-basis:100%">
            <details>
                <summary class="small">Lihat isi pesan yang akan dikirim</summary>
                <pre style="white-space:pre-wrap;font:inherit;margin:.6rem 0 0;padding:.8rem 1rem;background:#f9fafb;border:1px solid var(--line);border-radius:8px">Assalamualaikum, Bapak/Ibu {{ $r->parent_name }}.

{{ $r->stageMessage() }}

Pantau status pendaftaran: {{ route('ppdb.status', $r->school) }}

{{ $r->school->name }}</pre>
                <small>Tombol membuka WhatsApp di perangkat Anda. Pesan terkirim dari akun WhatsApp yang sedang login di perangkat itu.</small>
            </details>
        </div>
    @endif
</div>

<div class="grid-2">

    {{-- ================= Kiri: isian formulir ================= --}}
    <div>
        <section class="pd-card">
            <h2>Isian Formulir PPDB</h2>

            <div class="pd-grid">
                @php $prevSection = null; @endphp
                @foreach ($items as $it)
                    @if (! empty($it['section']) && $it['section'] !== $prevSection)
                        <div class="pd-sec">{{ $it['section'] }}</div>
                    @endif
                    @php
                        $prevSection = $it['section'] ?? null;
                        $v = $it['value'];
                        $text = is_array($v) ? implode(', ', $v) : (string) $v;
                        $wide = in_array($it['type'], ['paragraph', 'region'], true) || mb_strlen($text) > 60;
                    @endphp
                    <div class="pd-item {{ $wide ? 'wide' : '' }}">
                        <span class="pd-lbl">{{ $it['label'] }}</span>
                        <div class="pd-val">
                            @if ($it['type'] === 'file')
                                @if ($it['url'])
                                    <a class="pd-file" href="{{ $it['url'] }}" target="_blank" rel="noopener">Lihat berkas ↗</a>
                                @else
                                    <span class="pd-nofile">Belum diunggah</span>
                                @endif
                            @elseif (filled($v))
                                {!! nl2br(e($text)) !!}
                            @else
                                <span class="muted">-</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="pd-card">
            <h2>Catatan untuk pendaftar</h2>
            <form method="post" action="{{ route('admin.ppdb.update', $r->id) }}">
                @csrf @method('PUT')
                <input type="hidden" name="action" value="note">
                <div class="field">
                    <textarea name="admin_note" rows="3" maxlength="1000">{{ old('admin_note', $r->admin_note) }}</textarea>
                    <small>Tampil di halaman status orang tua dan ikut di email serta pesan WhatsApp.</small>
                </div>
                <button class="btn btn-sm" type="submit">Simpan catatan</button>
            </form>
        </section>
    </div>

    {{-- ================= Kanan: tahapan ================= --}}
    <div>

        {{-- Tahap 1 --}}
        <details class="pd-stage {{ $r->stage === 1 ? 'is-current' : '' }}" @if ($r->stage === 1) open @endif>
            <summary><span>Tahap 1 — Pengumpulan berkas @if ($r->stage === 1)<span class="pd-stage-tag">Sedang berjalan</span>@endif</span></summary>
            <div class="pd-stage-body">
                <form method="post" action="{{ route('admin.ppdb.update', $r->id) }}" style="margin-top:1rem">
                    @csrf @method('PUT')
                    <input type="hidden" name="action" value="docs">
                    <label class="check" style="margin-bottom:.8rem">
                        <input type="checkbox" name="docs_verified" value="1" @checked($r->docs_verified)>
                        Berkas sudah diperiksa dan lengkap
                    </label>
                    @unless ($r->docsComplete())
                        <p class="small muted">Masih ada berkas yang belum diunggah.</p>
                    @endunless
                    <button class="btn btn-sm" type="submit">Simpan pemeriksaan</button>
                </form>

                @if ($r->stage === 1 && $r->status === 'diproses')
                    <form method="post" action="{{ route('admin.ppdb.update', $r->id) }}" style="margin-top:1rem">
                        @csrf @method('PUT')
                        <input type="hidden" name="action" value="advance">
                        <button class="btn" type="submit" @disabled(! $r->docs_verified)>Lolos Tahap 1 → Seleksi</button>
                        @unless ($r->docs_verified)<p class="small muted" style="margin-top:.4rem">Tandai berkas lengkap dulu.</p>@endunless
                    </form>
                @endif
            </div>
        </details>

        {{-- Tahap 2 --}}
        @if ($r->stage >= 2)
            <details class="pd-stage {{ $r->stage === 2 ? 'is-current' : '' }}" @if ($r->stage === 2) open @endif>
                <summary><span>Tahap 2 — Seleksi @if ($r->stage === 2)<span class="pd-stage-tag">Sedang berjalan</span>@endif</span></summary>
                <div class="pd-stage-body">
                    <h3>Jadwal tes</h3>
                    <form method="post" action="{{ route('admin.ppdb.update', $r->id) }}">
                        @csrf @method('PUT')
                        <input type="hidden" name="action" value="test">
                        <div class="field">
                            <label for="test_at">Tanggal &amp; jam tes</label>
                            <input id="test_at" type="datetime-local" name="test_at"
                                   value="{{ old('test_at', $r->test_at?->format('Y-m-d\TH:i')) }}">
                        </div>
                        <div class="field">
                            <label for="test_mode">Cara tes</label>
                            <select id="test_mode" name="test_mode" style="max-width:320px">
                                <option value="">— pilih —</option>
                                @foreach ($testModes as $key => $label)
                                    <option value="{{ $key }}" @selected(old('test_mode', $r->test_mode) === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label for="test_place">Tempat (offline) atau tautan/platform (online)</label>
                            <input id="test_place" type="text" name="test_place" maxlength="255"
                                   value="{{ old('test_place', $r->test_place) }}"
                                   placeholder="Contoh: Ruang Kelas 1 / https://meet.google.com/xxx">
                        </div>
                        <div class="field">
                            <label for="test_info">Catatan untuk orang tua</label>
                            <textarea id="test_info" name="test_info" rows="2" maxlength="1000">{{ old('test_info', $r->test_info) }}</textarea>
                            <small>Contoh: bawa alat tulis, datang 15 menit lebih awal. Pendaftar diberi tahu lewat email saat jadwal disimpan.</small>
                        </div>
                        <button class="btn btn-sm" type="submit">Simpan jadwal &amp; beri tahu</button>
                    </form>

                    <hr>

                    <h3 style="margin-top:0">Nilai tes</h3>
                    <form method="post" action="{{ route('admin.ppdb.update', $r->id) }}">
                        @csrf @method('PUT')
                        <input type="hidden" name="action" value="score">
                        <div class="field">
                            <label for="test_score">Nilai tes (0–100)</label>
                            <input id="test_score" type="number" name="test_score" step="0.01" min="0" max="100"
                                   value="{{ old('test_score', $r->test_score) }}" style="max-width:160px">
                        </div>
                        <div class="field">
                            <label for="test_note">Catatan penilaian (internal, tidak dikirim)</label>
                            <textarea id="test_note" name="test_note" rows="2" maxlength="1000">{{ old('test_note', $r->test_note) }}</textarea>
                        </div>
                        <button class="btn btn-sm" type="submit">Simpan nilai</button>
                    </form>

                    @if ($r->stage === 2 && $r->status === 'diproses')
                        <form method="post" action="{{ route('admin.ppdb.update', $r->id) }}" style="margin-top:1rem">
                            @csrf @method('PUT')
                            <input type="hidden" name="action" value="advance">
                            <button class="btn" type="submit" @disabled($r->test_score === null)>Lolos Seleksi → Wawancara</button>
                            @if ($r->test_score === null)<p class="small muted" style="margin-top:.4rem">Isi nilai tes dulu.</p>@endif
                        </form>
                    @endif
                </div>
            </details>
        @endif

        {{-- Tahap 3 --}}
        @if ($r->stage >= 3)
            <details class="pd-stage {{ $r->stage === 3 ? 'is-current' : '' }}" @if ($r->stage === 3) open @endif>
                <summary><span>Tahap 3 — Wawancara @if ($r->stage === 3)<span class="pd-stage-tag">Sedang berjalan</span>@endif</span></summary>
                <div class="pd-stage-body">
                    <form method="post" action="{{ route('admin.ppdb.update', $r->id) }}" style="margin-top:1rem">
                        @csrf @method('PUT')
                        <input type="hidden" name="action" value="interview">
                        <div class="field">
                            <label for="interview_at">Tanggal &amp; jam</label>
                            <input id="interview_at" type="datetime-local" name="interview_at"
                                   value="{{ old('interview_at', $r->interview_at?->format('Y-m-d\TH:i')) }}">
                        </div>
                        <div class="field">
                            <label for="interview_place">Tempat</label>
                            <input id="interview_place" type="text" name="interview_place" maxlength="200"
                                   value="{{ old('interview_place', $r->interview_place) }}" placeholder="Contoh: Ruang Kepala Sekolah">
                        </div>
                        <div class="field">
                            <label for="interview_note">Catatan untuk orang tua</label>
                            <textarea id="interview_note" name="interview_note" rows="2" maxlength="1000">{{ old('interview_note', $r->interview_note) }}</textarea>
                            <small>Contoh: bawa dokumen asli. Pendaftar diberi tahu lewat email saat jadwal disimpan.</small>
                        </div>
                        <button class="btn btn-sm" type="submit">Simpan jadwal &amp; beri tahu</button>
                    </form>
                </div>
            </details>
        @endif

        {{-- Hasil akhir --}}
        <details class="pd-stage {{ $done ? 'is-current' : '' }}" @if ($done) open @endif>
            <summary><span>Hasil akhir @if ($done)<span class="pd-stage-tag">Sedang berjalan</span>@endif</span></summary>
            <div class="pd-stage-body">
                <form method="post" action="{{ route('admin.ppdb.update', $r->id) }}" style="margin-top:1rem">
                    @csrf @method('PUT')
                    <input type="hidden" name="action" value="finish">
                    <div class="field">
                        <label for="result">Keputusan</label>
                        <select id="result" name="result" style="max-width:260px">
                            <option value="diterima" @selected($r->status === 'diterima')>Diterima</option>
                            <option value="ditolak" @selected($r->status === 'ditolak')>Tidak diterima</option>
                        </select>
                        <small>"Diterima" hanya bisa setelah Tahap 3 dan jadwal wawancara terisi. "Tidak diterima" bisa kapan saja.</small>
                    </div>
                    <button class="btn btn-sm" type="submit">Simpan hasil &amp; beri tahu</button>
                </form>

                @if ($r->stage > 1)
                    <form method="post" action="{{ route('admin.ppdb.update', $r->id) }}" style="margin-top:1rem"
                          onsubmit="return confirm('Kembalikan ke tahap sebelumnya?')">
                        @csrf @method('PUT')
                        <input type="hidden" name="action" value="back">
                        <button class="link-btn" type="submit">← Kembalikan ke tahap sebelumnya</button>
                    </form>
                @endif
            </div>
        </details>

        <form class="pd-danger" method="post" action="{{ route('admin.ppdb.destroy', $r->id) }}"
              onsubmit="return confirm('Hapus pendaftar ini beserta berkasnya? Tindakan ini tidak bisa dibatalkan.')">
            @csrf @method('DELETE')
            <button class="link-btn danger" type="submit">Hapus data pendaftar</button>
        </form>
    </div>
</div>
@endsection