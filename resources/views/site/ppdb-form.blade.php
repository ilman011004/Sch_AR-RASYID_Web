@extends('layouts.site')

@section('title', 'Formulir Pendaftaran — '.$school->name)

@section('content')
@php
    $pages = $schema['pages'] ?? [];
    $totalPages = count($pages);
@endphp

{{-- Banner atas: sama dengan halaman Profil (breadcrumb + judul besar) --}}
<section class="page-title">
    <div class="wrap">
        <p class="crumb">
            <a href="{{ route('home', $school) }}">Beranda</a> /
            <a href="{{ route('ppdb', $school) }}">Pendaftaran</a> / Formulir
        </p>
        <h1>Formulir Pendaftaran</h1>
    </div>
</section>

<style>
    /* ---------- Tata letak: langkah di kiri, isian di kanan ---------- */
    .pf-layout { display: grid; grid-template-columns: 290px minmax(0, 1fr); gap: 2rem; align-items: start; }
    @media (max-width: 880px) { .pf-layout { grid-template-columns: 1fr; gap: 1.2rem; } }

    /* ---------- Panel samping ---------- */
    .pf-side { position: sticky; top: 96px; display: grid; gap: 1rem; }
    @media (max-width: 880px) { .pf-side { position: static; } }

    .pf-box { background: #fff; border: 1px solid var(--line); border-radius: 14px; padding: 1.2rem 1.3rem; }
    .pf-box h2 { margin: 0 0 .15rem; font-size: 1.05rem; line-height: 1.3; }
    .pf-box .pf-sub { margin: 0; font-size: .86rem; color: var(--muted); }

    .pf-who { display: flex; align-items: center; gap: .7rem; }
    .pf-avatar {
        width: 40px; height: 40px; border-radius: 50%; flex: none; display: grid; place-items: center;
        background: color-mix(in srgb, var(--primary) 12%, #fff); color: var(--primary); font-weight: 800;
    }
    .pf-who b { display: block; font-size: .92rem; line-height: 1.25; }
    .pf-who span { display: block; font-size: .8rem; color: var(--muted); word-break: break-all; }

    .pf-steps { list-style: none; margin: 0; padding: 0; counter-reset: s; }
    .pf-step { position: relative; display: flex; align-items: center; gap: .75rem; padding: .55rem 0; font-size: .92rem; color: #64748b; font-weight: 600; }
    .pf-step + .pf-step::before {
        content: ""; position: absolute; left: 15px; top: -9px; height: 18px; width: 2px; background: #e2e8f0;
    }
    .pf-step-dot {
        width: 32px; height: 32px; border-radius: 50%; flex: none; display: grid; place-items: center;
        border: 2px solid #cbd5e1; background: #fff; font-size: .85rem; font-weight: 800; color: #64748b; transition: .2s;
    }
    .pf-step.is-active { color: var(--primary); }
    .pf-step.is-active .pf-step-dot { background: var(--primary); border-color: var(--primary); color: #fff; }
    .pf-step.is-done { color: #0f172a; cursor: pointer; }
    .pf-step.is-done .pf-step-dot { background: var(--accent, #22a06b); border-color: var(--accent, #22a06b); color: #fff; }
    .pf-step.is-done:hover { text-decoration: underline; }

    @media (max-width: 880px) {
        .pf-steps { display: flex; gap: .4rem; overflow-x: auto; padding-bottom: .2rem; }
        .pf-step { flex: none; padding: .35rem .8rem .35rem .35rem; border: 1px solid var(--line); border-radius: 999px; background: #fff; }
        .pf-step + .pf-step::before { display: none; }
        .pf-step-dot { width: 26px; height: 26px; font-size: .78rem; }
    }

    /* ---------- Isi utama ---------- */
    .pf-main { background: #fff; border: 1px solid var(--line); border-radius: 14px; padding: 1.8rem 2rem; }
    @media (max-width: 560px) { .pf-main { padding: 1.3rem 1.1rem; } }

    .pf-progress { display: flex; align-items: center; gap: .8rem; margin-bottom: 1.4rem; font-size: .82rem; font-weight: 700; color: var(--muted); }
    .pf-bar { flex: 1; height: 6px; border-radius: 999px; background: #e2e8f0; overflow: hidden; }
    .pf-bar i { display: block; height: 100%; width: 0; background: var(--primary); border-radius: 999px; transition: width .3s ease; }

    .pf-panel { display: none; }
    .pf-panel.is-visible { display: block; animation: pfIn .25s ease; }
    @keyframes pfIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: none; } }

    .pf-head { margin-bottom: 1.4rem; padding-bottom: 1rem; border-bottom: 1px solid var(--line); }
    .pf-head h2 { margin: 0 0 .25rem; font-size: 1.4rem; }
    .pf-head p { margin: 0; color: var(--muted); font-size: .94rem; }

    /* Isian: dua kolom pada layar lebar, tanpa kotak per pertanyaan */
    .pf-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.1rem 1.3rem; }
    @media (max-width: 640px) { .pf-grid { grid-template-columns: 1fr; } }
    .pf-full { grid-column: 1 / -1; }

    .pf-label { display: block; margin-bottom: .35rem; font-size: .9rem; font-weight: 700; color: #1e293b; line-height: 1.35; }
    .pf-req { color: #dc2626; margin-left: 2px; }
    .pf-help { margin: -.15rem 0 .45rem; font-size: .8rem; color: var(--muted); line-height: 1.4; }
    .pf-field input[type="text"], .pf-field input[type="email"], .pf-field input[type="number"], .pf-field input[type="date"],
    .pf-field input[type="file"], .pf-field select, .pf-field textarea { width: 100%; }

    .pf-options { display: grid; gap: .55rem; }
    .pf-opt { display: flex; align-items: center; gap: .6rem; font-size: .94rem; color: #334155; cursor: pointer; }
    .pf-opt input { width: 18px; height: 18px; accent-color: var(--primary); margin: 0; flex: none; }
    .pf-options.is-inline { display: flex; flex-wrap: wrap; gap: .6rem 1.4rem; }

    /* Judul bagian (mis. Data Ayah / Data Ibu) */
    .pf-section { grid-column: 1 / -1; margin-top: .9rem; padding-bottom: .55rem; border-bottom: 2px solid color-mix(in srgb, var(--primary) 30%, #fff); }
    .pf-section:first-child { margin-top: 0; }
    .pf-section h3 { margin: 0; font-size: 1.1rem; color: var(--primary); }
    .pf-section p { margin: .2rem 0 0; font-size: .85rem; color: var(--muted); line-height: 1.45; }

    /* Alamat bertingkat */
    .pf-region-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .9rem; }
    @media (max-width: 560px) { .pf-region-grid { grid-template-columns: 1fr; } }
    .pf-region-lbl { display: block; margin-bottom: .25rem; font-size: .8rem; font-weight: 700; color: #475569; }
    .pf-region select:disabled { background: #f1f5f9; color: #94a3b8; cursor: not-allowed; }
    .pf-region-note { margin: .6rem 0 0; font-size: .8rem; color: #b45309; }

    .pf-hint { display: block; margin-top: .4rem; font-size: .8rem; color: var(--muted); }

    /* Tombol navigasi */
    .pf-nav { display: flex; justify-content: space-between; align-items: center; gap: 1rem; margin-top: 2rem; padding-top: 1.4rem; border-top: 1px solid var(--line); }
    .pf-nav .btn { padding: .75rem 1.7rem; }
    .pf-note { margin: 1rem 0 0; font-size: .82rem; color: var(--muted); }
</style>

<section class="section" style="padding-top: 2rem">
    <div class="wrap">

        @if (isset($myRegistrations) && $myRegistrations->isNotEmpty())
            <div style="background:#f0fdf4;border:1px solid #86efac;border-radius:12px;padding:1.1rem 1.4rem;margin-bottom:1.5rem;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem">
                <div>
                    <h3 style="margin:0;font-size:1rem;color:#166534">Anda sudah memiliki {{ $myRegistrations->count() }} pendaftaran di {{ $school->name }}</h3>
                    <p style="margin:.25rem 0 0;font-size:.85rem;color:#15803d">
                        Calon siswa terdaftar: <strong>{{ $myRegistrations->pluck('student_name')->implode(', ') }}</strong>.
                        Jika ingin mendaftarkan anak/saudara lainnya, silakan lanjutkan pengisian formulir di bawah ini.
                    </p>
                </div>
                <div>
                    <a href="{{ route('ppdb.status', $school) }}" class="btn btn-sm" style="background:#166534;border-color:#166534;font-size:.82rem;font-weight:700">
                        Lihat Status & Bukti Pendaftaran →
                    </a>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert err-box" style="margin-bottom: 1.5rem">
                Ada isian yang belum lengkap atau belum benar. Silakan periksa formulir Anda.
            </div>
        @endif

        <div class="pf-layout">

            {{-- ===== Panel samping ===== --}}
            <aside class="pf-side">
                <div class="pf-box">
                    <h2>{{ $school->name }}</h2>
                    <p class="pf-sub">Penerimaan Peserta Didik Baru{{ $school->ppdb_year ? ' · TA '.$school->ppdb_year : '' }}</p>
                </div>

                <div class="pf-box">
                    <div class="pf-who">
                        <div class="pf-avatar">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</div>
                        <div>
                            <b>{{ auth()->user()->name }}</b>
                            <span>{{ auth()->user()->email }}</span>
                        </div>
                    </div>
                </div>

                @if ($totalPages > 1)
                    <div class="pf-box">
                        <ol class="pf-steps" id="pfSteps">
                            @foreach ($pages as $idx => $p)
                                <li class="pf-step {{ $idx === 0 ? 'is-active' : '' }}" data-step="{{ $idx }}">
                                    <span class="pf-step-dot">{{ $idx + 1 }}</span>
                                    <span>{{ $p['title'] }}</span>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                @endif
            </aside>

            {{-- ===== Formulir ===== --}}
            <div class="pf-main">

                <div class="pf-progress">
                    <span id="pfCount">Langkah 1 dari {{ max($totalPages, 1) }}</span>
                    <div class="pf-bar"><i id="pfBar"></i></div>
                </div>

                <form method="post" action="{{ route('ppdb.store', $school) }}" enctype="multipart/form-data" class="form" id="ppdbUserForm">
                    @csrf
                    <div class="hp" aria-hidden="true">
                        <label>Jangan diisi <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                    </div>

                    @foreach ($pages as $pIdx => $p)
                        <div class="pf-panel {{ $pIdx === 0 ? 'is-visible' : '' }}" data-page-panel="{{ $pIdx }}">

                            <div class="pf-head">
                                <h2>{{ $p['title'] }}</h2>
                                @if (!empty($p['subtitle']))
                                    <p>{{ $p['subtitle'] }}</p>
                                @endif
                            </div>

                            <div class="pf-grid">
                                @foreach ($p['fields'] ?? [] as $f)
                                    @if (($f['type'] ?? '') === 'section')
                                        <div class="pf-section">
                                            <h3>{{ $f['label'] }}</h3>
                                            @if (!empty($f['help']))
                                                <p>{{ $f['help'] }}</p>
                                            @endif
                                        </div>
                                        @continue
                                    @endif

                                    @php
                                        $fId = $f['id'];
                                        $type = $f['type'] ?? 'text';
                                        $req = !empty($f['required']);
                                        $oldVal = old($fId);
                                        $opts = $f['options'] ?? [];
                                        $isFull = in_array($type, ['paragraph', 'checkbox', 'region', 'file'], true)
                                            || ($type === 'radio' && count($opts) > 3);
                                        $isGroup = in_array($type, ['radio', 'checkbox', 'region'], true);

                                        // Aturan isian untuk jawaban singkat / angka (sama dengan yang dicek server).
                                        $fmt = $f['format'] ?? '';
                                        if ($fmt === '') {
                                            $fmt = match ($f['mapping'] ?? null) {
                                                'nik' => 'nik',
                                                'parent_phone' => 'phone',
                                                'parent_email' => 'email',
                                                default => '',
                                            };
                                            if ($fmt === '') {
                                                $lblLower = mb_strtolower(trim($f['label'] ?? ''));
                                                if (preg_match('/\bnik\b/u', $lblLower)) {
                                                    $fmt = 'nik';
                                                } elseif (preg_match('/(nomor|no\.?)\s*(hp|wa|whatsapp|ponsel|telepon|handphone)|(\bhp\b|\bwa\b|\bwhatsapp\b)/u', $lblLower)) {
                                                    $fmt = 'phone';
                                                } elseif (preg_match('/\b(email|surel)\b/u', $lblLower)) {
                                                    $fmt = 'email';
                                                }
                                            }
                                        }
                                        $minLen = !empty($f['min']) ? (int) $f['min'] : null;
                                        $maxLen = !empty($f['max']) ? (int) $f['max'] : 255;
                                    @endphp

                                    <div class="pf-field {{ $isFull ? 'pf-full' : '' }}">
                                        @if ($isGroup)
                                            <span class="pf-label">{{ $f['label'] }}@if ($req)<span class="pf-req">*</span>@endif</span>
                                        @else
                                            <label class="pf-label" for="{{ $fId }}">{{ $f['label'] }}@if ($req)<span class="pf-req">*</span>@endif</label>
                                        @endif

                                        @if (!empty($f['help']))
                                            <div class="pf-help">{{ $f['help'] }}</div>
                                        @endif

                                        @if ($type === 'paragraph')
                                            <textarea id="{{ $fId }}" name="{{ $fId }}" rows="4" @if ($req) required @endif maxlength="2000" placeholder="Tulis jawaban Anda">{{ $oldVal }}</textarea>

                                        @elseif ($type === 'number' && $fmt === 'nik')
                                            <input type="text" id="{{ $fId }}" name="{{ $fId }}" value="{{ $oldVal }}" @if ($req) required @endif
                                                   inputmode="numeric" autocomplete="off" minlength="16" maxlength="16" pattern="[0-9]{16}"
                                                   title="NIK harus tepat 16 angka"
                                                   oninput="this.value=this.value.replace(/\D/g,''); validateNikField(this);"
                                                   placeholder="16 digit NIK">
                                            <small class="pf-hint">NIK harus tepat 16 digit angka sesuai KTP / Kartu Keluarga.</small>

                                        @elseif ($type === 'number' && $fmt === 'phone')
                                            <input type="text" id="{{ $fId }}" name="{{ $fId }}" value="{{ $oldVal }}" @if ($req) required @endif
                                                   inputmode="tel" autocomplete="tel" minlength="10" maxlength="15" pattern="(\+62|62|0)8[0-9]{8,12}"
                                                   title="Nomor HP Indonesia 10-14 digit, contoh: 081234567890"
                                                   oninput="this.value=this.value.replace(/[^0-9+]/g,''); validatePhoneField(this);"
                                                   placeholder="Contoh: 081234567890">
                                            <small class="pf-hint">Nomor HP Indonesia minimal 10 digit dan maksimal 14 digit.</small>

                                        @elseif ($type === 'number')
                                            <input type="number" id="{{ $fId }}" name="{{ $fId }}" value="{{ $oldVal }}" @if ($req) required @endif>

                                        @elseif ($type === 'date')
                                            <input type="date" id="{{ $fId }}" name="{{ $fId }}" value="{{ $oldVal }}" @if ($req) required @endif>

                                        @elseif ($type === 'select')
                                            <select id="{{ $fId }}" name="{{ $fId }}" @if ($req) required @endif>
                                                <option value="">— Pilih salah satu —</option>
                                                @foreach ($opts as $opt)
                                                    <option value="{{ $opt }}" @selected($oldVal === $opt)>{{ $opt }}</option>
                                                @endforeach
                                            </select>

                                        @elseif ($type === 'radio')
                                            <div class="pf-options {{ count($opts) <= 3 ? 'is-inline' : '' }}">
                                                @foreach ($opts as $opt)
                                                    <label class="pf-opt">
                                                        <input type="radio" name="{{ $fId }}" value="{{ $opt }}" @checked($oldVal === $opt) @if ($req) required @endif>
                                                        <span>{{ $opt }}</span>
                                                    </label>
                                                @endforeach
                                            </div>

                                        @elseif ($type === 'checkbox')
                                            <div class="pf-options">
                                                @foreach ($opts as $opt)
                                                    @php $checked = is_array($oldVal) && in_array($opt, $oldVal, true); @endphp
                                                    <label class="pf-opt">
                                                        <input type="checkbox" name="{{ $fId }}[]" value="{{ $opt }}" @checked($checked)>
                                                        <span>{{ $opt }}</span>
                                                    </label>
                                                @endforeach
                                            </div>

                                        @elseif ($type === 'region')
                                            @php $oldRegion = is_array($oldVal) ? $oldVal : []; @endphp
                                            <div class="pf-region" data-region data-base="{{ asset('data/wilayah') }}" data-old="{{ json_encode($oldRegion) }}">
                                                <div class="pf-region-grid">
                                                    <div>
                                                        <label class="pf-region-lbl" for="{{ $fId }}_provinsi">Provinsi</label>
                                                        <select id="{{ $fId }}_provinsi" name="{{ $fId }}[provinsi]" data-level="provinsi" @if ($req) required @endif>
                                                            <option value="">Memuat data…</option>
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="pf-region-lbl" for="{{ $fId }}_kabupaten">Kabupaten / Kota</label>
                                                        <select id="{{ $fId }}_kabupaten" name="{{ $fId }}[kabupaten]" data-level="kabupaten" disabled @if ($req) required @endif>
                                                            <option value="">Pilih kabupaten / kota</option>
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="pf-region-lbl" for="{{ $fId }}_kecamatan">Kecamatan</label>
                                                        <select id="{{ $fId }}_kecamatan" name="{{ $fId }}[kecamatan]" data-level="kecamatan" disabled @if ($req) required @endif>
                                                            <option value="">Pilih kecamatan</option>
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="pf-region-lbl" for="{{ $fId }}_desa">Desa / Kelurahan</label>
                                                        <select id="{{ $fId }}_desa" name="{{ $fId }}[desa]" data-level="desa" disabled @if ($req) required @endif>
                                                            <option value="">Pilih desa / kelurahan</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div style="margin-top:.9rem">
                                                    <label class="pf-region-lbl" for="{{ $fId }}_detail">Nama jalan, RT/RW, nomor rumah</label>
                                                    <input type="text" id="{{ $fId }}_detail" name="{{ $fId }}[detail]" value="{{ $oldRegion['detail'] ?? '' }}" maxlength="255" @if ($req) required @endif placeholder="Contoh: Jl. Mawar No. 5, RT 02 / RW 01">
                                                </div>
                                                <p class="pf-region-note" data-region-note hidden>Data wilayah belum bisa dimuat. Silakan ketik nama wilayah secara manual.</p>
                                            </div>

                                        @elseif ($type === 'file')
                                            <input type="file" id="{{ $fId }}" name="{{ $fId }}" @if ($req) required @endif accept=".jpg,.jpeg,.png,.pdf">
                                            <small class="pf-hint">Format file yang diterima: JPG, PNG, atau PDF (maks. 2 MB)</small>

                                        @elseif ($fmt === 'nik')
                                            <input type="text" id="{{ $fId }}" name="{{ $fId }}" value="{{ $oldVal }}" @if ($req) required @endif
                                                   inputmode="numeric" autocomplete="off" minlength="16" maxlength="16" pattern="[0-9]{16}"
                                                   title="NIK harus tepat 16 digit angka"
                                                   oninput="this.value=this.value.replace(/\D/g,''); validateNikField(this);"
                                                   placeholder="16 digit angka NIK">
                                            <small class="pf-hint">NIK harus tepat 16 digit angka sesuai KTP / Kartu Keluarga.</small>

                                        @elseif ($fmt === 'phone')
                                            <input type="text" id="{{ $fId }}" name="{{ $fId }}" value="{{ $oldVal }}" @if ($req) required @endif
                                                   inputmode="tel" autocomplete="tel" minlength="10" maxlength="15" pattern="(\+62|62|0)8[0-9]{8,12}"
                                                   title="Nomor HP Indonesia 10-14 digit, contoh: 081234567890"
                                                   oninput="this.value=this.value.replace(/[^0-9+]/g,''); validatePhoneField(this);"
                                                   placeholder="Contoh: 081234567890">
                                            <small class="pf-hint">Nomor HP Indonesia minimal 10 digit dan maksimal 14 digit (contoh: 081234567890).</small>

                                        @elseif ($fmt === 'digits')
                                            <input type="text" id="{{ $fId }}" name="{{ $fId }}" value="{{ $oldVal }}" @if ($req) required @endif
                                                   inputmode="numeric" pattern="[0-9]*" title="Hanya boleh berisi angka"
                                                   @if ($minLen) minlength="{{ $minLen }}" @endif maxlength="{{ $maxLen }}"
                                                   oninput="this.value=this.value.replace(/\D/g,'')">
                                            @if ($minLen || !empty($f['max']))
                                                <small class="pf-hint">Hanya angka{{ $minLen ? ', minimal '.$minLen.' digit' : '' }}{{ !empty($f['max']) ? ', maksimal '.$maxLen.' digit' : '' }}.</small>
                                            @endif

                                        @elseif ($fmt === 'email')
                                            <input type="email" id="{{ $fId }}" name="{{ $fId }}" value="{{ $oldVal }}" @if ($req) required @endif
                                                   inputmode="email" autocomplete="email" maxlength="{{ $maxLen }}" placeholder="nama@email.com">

                                        @else
                                            <input type="text" id="{{ $fId }}" name="{{ $fId }}" value="{{ $oldVal }}" @if ($req) required @endif
                                                   @if ($minLen) minlength="{{ $minLen }}" @endif maxlength="{{ $maxLen }}">
                                            @if ($minLen || !empty($f['max']))
                                                <small class="pf-hint">{{ $minLen ? 'Minimal '.$minLen.' karakter' : '' }}{{ $minLen && !empty($f['max']) ? ', ' : '' }}{{ !empty($f['max']) ? ($minLen ? 'maksimal ' : 'Maksimal ').$maxLen.' karakter' : '' }}.</small>
                                            @endif
                                        @endif

                                        @error($fId)
                                            <span class="err" style="display:block;margin-top:.45rem">{{ $message }}</span>
                                        @enderror

                                        @if ($type === 'region' && $errors->has($fId.'.*'))
                                            <span class="err" style="display:block;margin-top:.45rem">{{ collect($errors->get($fId.'.*'))->flatten()->first() }}</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            {{-- Navigasi halaman --}}
                            <div class="pf-nav">
                                @if ($pIdx > 0)
                                    <button type="button" class="btn btn-outline" onclick="prevGformStep({{ $pIdx }})">← Sebelumnya</button>
                                @else
                                    <a href="{{ route('ppdb', $school) }}" class="btn btn-outline">← Kembali</a>
                                @endif

                                @if ($pIdx < $totalPages - 1)
                                    <button type="button" class="btn" onclick="nextGformStep({{ $pIdx }})">Selanjutnya →</button>
                                @else
                                    <button type="submit" class="btn" style="background:var(--accent,#22a06b);border-color:var(--accent,#22a06b);font-weight:800">Kirim Pendaftaran</button>
                                @endif
                            </div>

                            @if ($pIdx === $totalPages - 1)
                                <p class="pf-note">Pastikan seluruh data sudah benar sebelum dikirim. Nomor pendaftaran akan tampil setelah formulir berhasil dikirim.</p>
                            @endif
                        </div>
                    @endforeach
                </form>
            </div>
        </div>
    </div>
</section>

<script>
    let currentStep = 0;
    const totalSteps = {{ $totalPages }};

    function validateNikField(input) {
        const val = input.value.trim();
        if (val === '') {
            input.setCustomValidity(input.required ? 'NIK wajib diisi.' : '');
            return;
        }
        if (!/^\d+$/.test(val)) {
            input.setCustomValidity('NIK hanya boleh berisi angka.');
        } else if (val.length < 16) {
            input.setCustomValidity('NIK kurang lengkap: baru ' + val.length + ' dari 16 digit angka.');
        } else if (val.length > 16) {
            input.setCustomValidity('NIK terlalu panjang: harus tepat 16 digit angka.');
        } else {
            input.setCustomValidity('');
        }
    }

    function validatePhoneField(input) {
        let val = input.value.trim();
        if (val === '') {
            input.setCustomValidity(input.required ? 'Nomor HP wajib diisi.' : '');
            return;
        }
        // Bersihkan tanda spasi atau strip jika ada
        const clean = val.replace(/[\s\-]/g, '');
        if (!/^(\+62|62|0)8[0-9]{8,12}$/.test(clean)) {
            if (!/^[0-9+]+$/.test(clean)) {
                input.setCustomValidity('Nomor HP hanya boleh berisi angka.');
            } else if (clean.length < 10) {
                input.setCustomValidity('Nomor HP kurang: baru ' + clean.length + ' digit (minimal 10 digit, contoh: 081234567890).');
            } else if (clean.length > 15) {
                input.setCustomValidity('Nomor HP terlalu panjang: maksimal 14 digit.');
            } else if (!clean.startsWith('08') && !clean.startsWith('628') && !clean.startsWith('+628')) {
                input.setCustomValidity('Nomor HP harus diawali 08... atau +628... (contoh: 081234567890).');
            } else {
                input.setCustomValidity('Format nomor HP tidak valid. Contoh yang benar: 081234567890.');
            }
        } else {
            input.setCustomValidity('');
        }
    }

    function showGformStep(index, noScroll) {
        document.querySelectorAll('.pf-panel').forEach(function (panel, i) {
            panel.classList.toggle('is-visible', i === index);
        });

        document.querySelectorAll('.pf-step').forEach(function (node, i) {
            node.classList.remove('is-active', 'is-done');
            if (i < index) { node.classList.add('is-done'); }
            else if (i === index) { node.classList.add('is-active'); }
        });

        document.getElementById('pfCount').textContent = 'Langkah ' + (index + 1) + ' dari ' + Math.max(totalSteps, 1);
        document.getElementById('pfBar').style.width = (((index + 1) / Math.max(totalSteps, 1)) * 100) + '%';

        currentStep = index;
        if (!noScroll) {
            const top = document.querySelector('.pf-layout').getBoundingClientRect().top + window.scrollY - 90;
            window.scrollTo({ top: top, behavior: 'smooth' });
        }
    }

    function nextGformStep(index) {
        const panel = document.querySelector('.pf-panel[data-page-panel="' + index + '"]');
        const inputs = panel.querySelectorAll('input, select, textarea');
        let isValid = true;

        for (const input of inputs) {
            if (!input.checkValidity()) {
                input.reportValidity();
                isValid = false;
                break;
            }
        }

        if (isValid && index < totalSteps - 1) {
            showGformStep(index + 1);
        }
    }

    function prevGformStep(index) {
        if (index > 0) { showGformStep(index - 1); }
    }

    // Klik langkah yang sudah selesai di panel samping untuk kembali ke halaman itu.
    document.querySelectorAll('.pf-step').forEach(function (node, i) {
        node.addEventListener('click', function () {
            if (node.classList.contains('is-done')) { showGformStep(i); }
        });
    });

    // Jika server mengembalikan error validasi, langsung buka halaman yang berisi error pertama.
    (function () {
        let start = 0;
        const firstErr = document.querySelector('.pf-panel .err');
        if (firstErr) {
            const panel = firstErr.closest('.pf-panel');
            start = parseInt(panel.dataset.pagePanel, 10) || 0;
        }
        showGformStep(start, true);
    })();

    // ---------- Alamat bertingkat: Provinsi > Kabupaten/Kota > Kecamatan > Desa/Kelurahan ----------
    // Data dimuat bertahap dari public/data/wilayah (dibuat dengan tools/build-wilayah.py).
    // Nilai yang dikirim ke server adalah NAMA wilayah; kodenya hanya dipakai untuk memuat tingkat berikutnya.
    (function () {
        const cache = {};
        const LEVELS = ['provinsi', 'kabupaten', 'kecamatan', 'desa'];

        function load(url) {
            if (!cache[url]) {
                cache[url] = fetch(url, { credentials: 'same-origin' }).then(function (r) {
                    if (!r.ok) { throw new Error('HTTP ' + r.status); }
                    return r.json();
                });
                cache[url].catch(function () { delete cache[url]; });
            }
            return cache[url];
        }

        function fill(select, items, placeholder, selectedName) {
            select.innerHTML = '';
            const first = document.createElement('option');
            first.value = '';
            first.textContent = placeholder;
            select.appendChild(first);
            let chosen = false;
            items.forEach(function (it) {
                const o = document.createElement('option');
                o.value = it[1];
                o.textContent = it[1];
                o.dataset.code = it[0];
                if (selectedName && it[1] === selectedName) { o.selected = true; chosen = true; }
                select.appendChild(o);
            });
            select.disabled = items.length === 0;
            return chosen;
        }

        function reset(select, placeholder) {
            select.innerHTML = '<option value="">' + placeholder + '</option>';
            select.disabled = true;
        }

        function codeOf(select) {
            const o = select.options[select.selectedIndex];
            return o ? (o.dataset.code || '') : '';
        }

        // Jika salah satu tingkat tidak tersedia di database lokal, izinkan ketik manual hanya pada tingkat yang kosong/gagal tersebut tanpa mereset tingkat lainnya
        function enableManualForLevel(select, placeholder) {
            if (!select || select.tagName === 'INPUT') return;
            const input = document.createElement('input');
            input.type = 'text';
            input.name = select.name;
            input.id = select.id;
            input.required = select.required;
            input.maxLength = 120;
            input.className = select.className;
            input.placeholder = placeholder || 'Ketik manual...';
            input.value = '';
            select.replaceWith(input);
        }

        // Jika benar-benar gagal total saat awal muat provinsi (misal JSON provinsi hilang)
        function toManual(box) {
            box.querySelectorAll('select[data-level]').forEach(function (sel) {
                if (sel.tagName === 'INPUT') return;
                const input = document.createElement('input');
                input.type = 'text';
                input.name = sel.name;
                input.id = sel.id;
                input.required = sel.required;
                input.maxLength = 120;
                input.placeholder = sel.closest('div').querySelector('label') ? sel.closest('div').querySelector('label').textContent : 'Ketik wilayah...';
                input.value = '';
                sel.replaceWith(input);
            });
            const note = box.querySelector('[data-region-note]');
            if (note) note.hidden = false;
        }

        function initRegion(box) {
            const base = box.dataset.base.replace(/\/$/, '');
            let old = {};
            try { old = JSON.parse(box.dataset.old || '{}') || {}; } catch (e) { old = {}; }
            const s = {};
            LEVELS.forEach(function (l) { s[l] = box.querySelector('[data-level="' + l + '"]'); });

            function loadKabupaten(provCode, keep) {
                return load(base + '/regencies.json').then(function (all) {
                    const list = all[provCode] || [];
                    if (list.length > 0) {
                        fill(s.kabupaten, list, 'Pilih kabupaten / kota', keep);
                    } else {
                        enableManualForLevel(s.kabupaten, 'Ketik nama kabupaten / kota');
                    }
                }).catch(function () {
                    enableManualForLevel(s.kabupaten, 'Ketik nama kabupaten / kota');
                });
            }
            function loadKecamatan(kabCode, keep) {
                return load(base + '/districts/' + kabCode.slice(0, 2) + '.json').then(function (all) {
                    const list = all[kabCode] || [];
                    if (list.length > 0) {
                        fill(s.kecamatan, list, 'Pilih kecamatan', keep);
                    } else {
                        enableManualForLevel(s.kecamatan, 'Ketik nama kecamatan');
                    }
                }).catch(function () {
                    enableManualForLevel(s.kecamatan, 'Ketik nama kecamatan');
                });
            }
            function loadDesa(kecCode, keep) {
                return load(base + '/villages/' + kecCode.slice(0, 4) + '.json').then(function (all) {
                    const list = all[kecCode] || [];
                    if (list.length > 0) {
                        fill(s.desa, list, 'Pilih desa / kelurahan', keep);
                    } else {
                        enableManualForLevel(s.desa, 'Ketik nama desa / kelurahan');
                    }
                }).catch(function () {
                    // Jika data desa wilayah lain belum tersedia di server, cukup tingkat desa yang bisa diketik manual
                    enableManualForLevel(s.desa, 'Ketik nama desa / kelurahan');
                });
            }

            s.provinsi.addEventListener('change', function () {
                reset(s.kabupaten, 'Pilih kabupaten / kota'); reset(s.kecamatan, 'Pilih kecamatan'); reset(s.desa, 'Pilih desa / kelurahan');
                const code = codeOf(s.provinsi);
                if (code) { loadKabupaten(code); }
            });
            s.kabupaten.addEventListener('change', function () {
                reset(s.kecamatan, 'Pilih kecamatan'); reset(s.desa, 'Pilih desa / kelurahan');
                const code = codeOf(s.kabupaten);
                if (code) { loadKecamatan(code); }
            });
            s.kecamatan.addEventListener('change', function () {
                reset(s.desa, 'Pilih desa / kelurahan');
                const code = codeOf(s.kecamatan);
                if (code) { loadDesa(code); }
            });

            // Muat provinsi; bila halaman dimuat ulang karena error validasi, pulihkan pilihan sebelumnya tingkat demi tingkat.
            load(base + '/provinces.json').then(function (provs) {
                fill(s.provinsi, provs, 'Pilih provinsi', old.provinsi);
                if (!old.provinsi || !codeOf(s.provinsi)) { return; }
                return loadKabupaten(codeOf(s.provinsi), old.kabupaten).then(function () {
                    if (!old.kabupaten || !codeOf(s.kabupaten)) { return; }
                    return loadKecamatan(codeOf(s.kabupaten), old.kecamatan).then(function () {
                        if (!old.kecamatan || !codeOf(s.kecamatan)) { return; }
                        return loadDesa(codeOf(s.kecamatan), old.desa);
                    });
                });
            }).catch(function () { toManual(box); });
        }

        // Inisialisasi awal validasi NIK dan HP jika sudah terisi (misalnya dari old value)
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('input[pattern="[0-9]{16}"]').forEach(validateNikField);
        document.querySelectorAll('input[pattern="(\\+62|62|0)8[0-9]{8,12}"]').forEach(validatePhoneField);
    });

    document.querySelectorAll('[data-region]').forEach(initRegion);
    })();
</script>
@endsection