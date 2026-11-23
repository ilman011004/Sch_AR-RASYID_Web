@extends('layouts.admin')

@section('title', 'Detail Pendaftar')

@section('content')
@php
    $done = $r->stage >= 4;
    $waLink = $r->waLink();
@endphp

<div class="page-head">
        <h1>{{ $r->student_name }} <small>{{ $r->reg_number }} · {{ $r->school->name }}</small></h1>
    <a href="{{ auth()->user()->isSuperAdmin() ? route('admin.ppdb.all') : route('admin.ppdb.index') }}">← Kembali ke daftar</a>
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

<section class="panel">
    <p style="margin:0 0 .6rem">
        Status: <span class="badge s-{{ $r->status }}">{{ $r->statusLabel() }}</span>
        @if ($waLink)
            &nbsp;<a class="btn btn-sm" href="{{ $waLink }}" target="_blank" rel="noopener">Kirim WhatsApp sesuai tahap</a>
        @endif
    </p>
    <details>
        <summary class="small">Lihat isi pesan yang akan dikirim</summary>
        <pre style="white-space:pre-wrap;font:inherit;margin:.6rem 0 0;padding:.8rem 1rem;background:#f9fafb;border:1px solid var(--line);border-radius:8px">Assalamualaikum, Bapak/Ibu {{ $r->parent_name }}.

{{ $r->stageMessage() }}

Pantau status pendaftaran: {{ route('ppdb.status', $r->school) }}

{{ $r->school->name }}</pre>
        <small>Tombol membuka WhatsApp di perangkat Anda. Pesan terkirim dari akun WhatsApp yang sedang login di perangkat itu.</small>
    </details>
</section>

<div class="grid-2">

    {{-- ================= Kiri: data & berkas ================= --}}
    <div>
        <section class="panel">
            <h2 style="margin-top:0">Data calon siswa</h2>
            <dl class="dl">
                <dt>Tanggal daftar</dt><dd>{{ $r->created_at->format('d/m/Y H:i') }}</dd>
                <dt>Nama</dt><dd>{{ $r->student_name }}</dd>
                <dt>NIK</dt><dd>{{ $r->nik ?: '-' }}</dd>
                <dt>Jenis kelamin</dt><dd>{{ $r->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</dd>
                <dt>Tempat, tgl lahir</dt><dd>{{ $r->birth_place }}, {{ $r->birth_date->format('d/m/Y') }}</dd>
                <dt>Agama</dt><dd>{{ $r->religion ?: '-' }}</dd>
                <dt>Alamat</dt><dd>{{ $r->address }}</dd>
                <dt>Asal sekolah</dt><dd>{{ $r->previous_school ?: '-' }}</dd>
            </dl>

            <h2>Orang tua / wali</h2>
            <dl class="dl">
                <dt>Nama</dt><dd>{{ $r->parent_name }}</dd>
                <dt>No. HP</dt><dd>{{ $r->parent_phone }}</dd>
                <dt>Email</dt><dd>{{ $r->parent_email ?: '-' }}</dd>
                <dt>Pekerjaan</dt><dd>{{ $r->parent_job ?: '-' }}</dd>
                <dt>Akun pendaftar</dt><dd>{{ $r->user?->email ?: '-' }}</dd>
            </dl>

            @if (! empty($r->custom_fields) && is_array($r->custom_fields))
                <h2>Isian Formulir PPDB</h2>
                <dl class="dl">
                    @foreach ($r->custom_fields as $cf)
                        <dt>{{ $cf['label'] ?? 'Pertanyaan' }}</dt>
                        <dd>
                            @php $val = $cf['value'] ?? null; @endphp
                            @if (($cf['type'] ?? '') === 'file' && filled($val))
                                <a href="{{ route('admin.ppdb.document', [$r->id, 'custom_'.($loop->index)]) }}" target="_blank" rel="noopener" style="font-weight:700">Lihat Berkas Terunggah ↗</a>
                            @elseif (is_array($val))
                                {{ implode(', ', $val) }}
                            @elseif (filled($val))
                                {!! nl2br(e($val)) !!}
                            @else
                                <span class="muted">-</span>
                            @endif
                        </dd>
                    @endforeach
                </dl>
            @endif
        </section>

        <section class="panel">
            <h2 style="margin-top:0">Berkas</h2>
            <ul class="plain">
                @foreach ($docs as $field => $label)
                    <li>
                        <strong>{{ $label }}:</strong>
                        @if ($r->{$field})
                            <a href="{{ route('admin.ppdb.document', [$r->id, $field]) }}" target="_blank" rel="noopener">Buka berkas</a>
                        @else
                            <span class="badge s-ditolak">Belum diunggah</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </section>

        <section class="panel">
            <h2 style="margin-top:0">Catatan untuk pendaftar</h2>
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
        <section class="panel stage-panel {{ $r->stage === 1 ? 'is-current' : '' }}">
            <h2 style="margin-top:0">Tahap 1 — Pengumpulan berkas</h2>

            <form method="post" action="{{ route('admin.ppdb.update', $r->id) }}">
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
        </section>

        {{-- Tahap 2 --}}
        @if ($r->stage >= 2)
            <section class="panel stage-panel {{ $r->stage === 2 ? 'is-current' : '' }}">
                <h2 style="margin-top:0">Tahap 2 — Seleksi</h2>

                <h3 style="font-size:1rem;margin:0 0 .6rem">Jadwal tes</h3>
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

                <h3 style="font-size:1rem;margin:0 0 .6rem">Nilai tes</h3>
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
            </section>
        @endif

        {{-- Tahap 3 --}}
        @if ($r->stage >= 3)
            <section class="panel stage-panel {{ $r->stage === 3 ? 'is-current' : '' }}">
                <h2 style="margin-top:0">Tahap 3 — Wawancara</h2>

                <form method="post" action="{{ route('admin.ppdb.update', $r->id) }}">
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
            </section>
        @endif

        {{-- Hasil akhir --}}
        <section class="panel stage-panel {{ $done ? 'is-current' : '' }}">
            <h2 style="margin-top:0">Hasil akhir</h2>
            <form method="post" action="{{ route('admin.ppdb.update', $r->id) }}">
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
        </section>

        <form method="post" action="{{ route('admin.ppdb.destroy', $r->id) }}"
              onsubmit="return confirm('Hapus pendaftar ini beserta berkasnya? Tindakan ini tidak bisa dibatalkan.')">
            @csrf @method('DELETE')
            <button class="link-btn danger" type="submit">Hapus data pendaftar</button>
        </form>
    </div>
</div>
@endsection