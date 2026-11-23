@extends('layouts.site')

@section('title', 'Formulir Pendaftaran — '.$school->name)

@section('content')
<style>
    .gform-user-container { max-width: 760px; margin-inline: auto; }

    /* Top banner card */
    .gform-top-card {
        background: #fff; border-radius: 16px; border-top: 10px solid var(--primary);
        box-shadow: 0 4px 18px rgba(0,0,0,.06); padding: 2rem 2.2rem; margin-bottom: 1.8rem;
        border-left: 1px solid var(--line); border-right: 1px solid var(--line); border-bottom: 1px solid var(--line);
    }
    .gform-top-card h1 { font-size: 1.85rem; margin: 0 0 .5rem; line-height: 1.25; color: #0f172a; }
    .gform-top-card p { margin: 0; color: #475569; font-size: .95rem; line-height: 1.5; }
    .gform-auth-pill {
        display: inline-flex; align-items: center; gap: .5rem; margin-top: 1.2rem;
        padding: .4rem .9rem; border-radius: 999px; background: #f1f5f9; font-size: .82rem; color: #334155; font-weight: 600;
    }

    /* Multi-step progress indicator */
    .gform-stepper {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 2rem; position: relative; gap: .6rem;
    }
    .gform-stepper::before {
        content: ""; position: absolute; top: 20px; left: 35px; right: 35px;
        height: 3px; background: #e2e8f0; z-index: 1;
    }
    .gform-step-node {
        position: relative; z-index: 2; display: flex; flex-direction: column;
        align-items: center; text-align: center; flex: 1;
    }
    .gform-step-dot {
        width: 40px; height: 40px; border-radius: 50%; background: #fff;
        border: 3px solid #cbd5e1; color: #64748b; font-weight: 800; font-size: 1rem;
        display: grid; place-items: center; transition: all .25s ease;
    }
    .gform-step-node.is-active .gform-step-dot {
        border-color: var(--primary); background: var(--primary); color: #fff;
        box-shadow: 0 0 0 5px color-mix(in srgb, var(--primary) 22%, transparent);
    }
    .gform-step-node.is-done .gform-step-dot {
        border-color: var(--accent, #22a06b); background: var(--accent, #22a06b); color: #fff;
    }
    .gform-step-name {
        margin-top: .45rem; font-size: .84rem; font-weight: 700; color: #64748b; line-height: 1.25;
    }
    .gform-step-node.is-active .gform-step-name { color: var(--primary); }
    .gform-step-node.is-done .gform-step-name { color: #0f172a; }

    /* Page section card */
    .gform-section-card {
        background: #fff; border: 1px solid var(--line); border-radius: 14px;
        padding: 1.8rem 2rem; margin-bottom: 1.8rem; box-shadow: 0 4px 14px rgba(0,0,0,.04);
        display: none;
    }
    .gform-section-card.is-visible { display: block; animation: stepFadeIn .25s ease; }
    @keyframes stepFadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }

    .gform-section-header {
        border-bottom: 2px solid #f8fafc; padding-bottom: 1rem; margin-bottom: 1.6rem;
    }
    .gform-section-header h2 { font-size: 1.45rem; margin: 0 0 .3rem; color: #0f172a; }
    .gform-section-header p { margin: 0; color: #64748b; font-size: .92rem; }

    /* Question Item Box (Google Form Card Style) */
    .gform-field-item {
        background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;
        padding: 1.3rem 1.4rem; margin-bottom: 1.4rem; transition: border-color .2s;
    }
    .gform-field-item:hover, .gform-field-item:focus-within {
        border-color: var(--primary);
    }
    .gform-field-label {
        display: block; font-size: 1rem; font-weight: 700; color: #1e293b; margin-bottom: .35rem; line-height: 1.35;
    }
    .gform-req-star { color: #ef4444; margin-left: 2px; }
    .gform-field-help { font-size: .82rem; color: var(--muted); margin-bottom: .8rem; }

    .gform-options-stack { display: grid; gap: .6rem; margin-top: .6rem; }
    .gform-opt-item {
        display: flex; align-items: center; gap: .6rem; font-size: .95rem; color: #334155; cursor: pointer;
    }
    .gform-opt-item input[type="radio"], .gform-opt-item input[type="checkbox"] {
        width: 18px; height: 18px; accent-color: var(--primary); margin: 0;
    }

    /* Footer Navigation */
    .gform-nav-bar {
        display: flex; justify-content: space-between; align-items: center;
        margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--line);
    }
    .gform-nav-bar .btn { padding: .75rem 1.8rem; font-size: 1rem; }
</style>

<section class="section" style="padding-top: 2rem">
    <div class="wrap gform-user-container">

        @if ($errors->any())
            <div class="alert err-box" style="margin-bottom: 1.5rem">
                Ada isian yang belum lengkap atau belum benar. Silakan periksa formulir Anda.
            </div>
        @endif

        @php
            $pages = $schema['pages'] ?? [];
            $totalPages = count($pages);
        @endphp

        {{-- Top Info Card --}}
        <div class="gform-top-card">
            <h1>Formulir Pendaftaran Siswa Baru</h1>
            <p>{{ $school->name }} {{ $school->ppdb_year ? 'Tahun Ajaran '.$school->ppdb_year : '' }}</p>
            <div class="gform-auth-pill">
                <span>👤 Akun Pendaftar:</span>
                <strong>{{ auth()->user()->name }}</strong> ({{ auth()->user()->email }})
            </div>
        </div>

        {{-- Multi-step Progress Bar (Jika Lebih dari 1 Halaman) --}}
        @if ($totalPages > 1)
            <div class="gform-stepper" id="gformStepper">
                @foreach ($pages as $idx => $p)
                    <div class="gform-step-node {{ $idx === 0 ? 'is-active' : '' }}" data-step="{{ $idx }}">
                        <div class="gform-step-dot">{{ $idx + 1 }}</div>
                        <div class="gform-step-name">{{ $p['title'] }}</div>
                    </div>
                @endforeach
            </div>
        @endif

        <form method="post" action="{{ route('ppdb.store', $school) }}" enctype="multipart/form-data" class="form" id="ppdbUserForm">
            @csrf
            <div class="hp" aria-hidden="true">
                <label>Jangan diisi <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
            </div>

            @foreach ($pages as $pIdx => $p)
                <div class="gform-section-card {{ $pIdx === 0 ? 'is-visible' : '' }}" data-page-panel="{{ $pIdx }}">
                    
                    <div class="gform-section-header">
                        <h2>{{ $p['title'] }}</h2>
                        @if (!empty($p['subtitle']))
                            <p>{{ $p['subtitle'] }}</p>
                        @endif
                    </div>

                    @foreach ($p['fields'] ?? [] as $f)
                        @php
                            $fId = $f['id'];
                            $req = !empty($f['required']);
                            $oldVal = old($fId);
                        @endphp

                        <div class="gform-field-item">
                            <label class="gform-field-label">
                                {{ $f['label'] }}
                                @if ($req)<span class="gform-req-star">*</span>@endif
                            </label>

                            @if (!empty($f['help']))
                                <div class="gform-field-help">{{ $f['help'] }}</div>
                            @endif

                            @if ($f['type'] === 'paragraph')
                                <textarea name="{{ $fId }}" rows="3" @if ($req) required @endif maxlength="2000" placeholder="Jawaban Anda...">{{ $oldVal }}</textarea>

                            @elseif ($f['type'] === 'number')
                                <input type="number" name="{{ $fId }}" value="{{ $oldVal }}" @if ($req) required @endif placeholder="Jawaban angka...">

                            @elseif ($f['type'] === 'date')
                                <input type="date" name="{{ $fId }}" value="{{ $oldVal }}" @if ($req) required @endif>

                            @elseif ($f['type'] === 'select')
                                <select name="{{ $fId }}" @if ($req) required @endif>
                                    <option value="">— Pilih Salah Satu —</option>
                                    @foreach ($f['options'] ?? [] as $opt)
                                        <option value="{{ $opt }}" @selected($oldVal === $opt)>{{ $opt }}</option>
                                    @endforeach
                                </select>

                            @elseif ($f['type'] === 'radio')
                                <div class="gform-options-stack">
                                    @foreach ($f['options'] ?? [] as $opt)
                                        <label class="gform-opt-item">
                                            <input type="radio" name="{{ $fId }}" value="{{ $opt }}" @checked($oldVal === $opt) @if ($req) required @endif>
                                            <span>{{ $opt }}</span>
                                        </label>
                                    @endforeach
                                </div>

                            @elseif ($f['type'] === 'checkbox')
                                <div class="gform-options-stack">
                                    @foreach ($f['options'] ?? [] as $opt)
                                        @php $checked = is_array($oldVal) && in_array($opt, $oldVal, true); @endphp
                                        <label class="gform-opt-item">
                                            <input type="checkbox" name="{{ $fId }}[]" value="{{ $opt }}" @checked($checked)>
                                            <span>{{ $opt }}</span>
                                        </label>
                                    @endforeach
                                </div>

                            @elseif ($f['type'] === 'file')
                                <input type="file" name="{{ $fId }}" @if ($req) required @endif accept=".jpg,.jpeg,.png,.pdf">
                                <small style="display:block;margin-top:.4rem;color:var(--muted)">Format file yang diterima: JPG, PNG, atau PDF (maks. 2 MB)</small>

                            @else
                                {{-- Default: Jawaban Singkat --}}
                                <input type="text" name="{{ $fId }}" value="{{ $oldVal }}" @if ($req) required @endif maxlength="255" placeholder="Jawaban Anda...">
                            @endif

                            @error($fId)
                                <span class="err" style="display:block;margin-top:.5rem">{{ $message }}</span>
                            @enderror
                        </div>
                    @endforeach

                    {{-- Navigasi Langkah Halaman --}}
                    <div class="gform-nav-bar">
                        @if ($pIdx > 0)
                            <button type="button" class="btn btn-outline" onclick="prevGformStep({{ $pIdx }})">← Halaman Sebelumnya</button>
                        @else
                            <div></div>
                        @endif

                        @if ($pIdx < $totalPages - 1)
                            <button type="button" class="btn" onclick="nextGformStep({{ $pIdx }})">Halaman Selanjutnya →</button>
                        @else
                            <button type="submit" class="btn" style="background:var(--accent,#22a06b);border-color:var(--accent,#22a06b);font-weight:800">
                                🚀 Kirim Pendaftaran
                            </button>
                        @endif
                    </div>

                </div>
            @endforeach

        </form>

    </div>
</section>

<script>
    let currentStep = 0;
    const totalSteps = {{ $totalPages }};

    function showGformStep(index) {
        document.querySelectorAll('.gform-section-card').forEach((panel, i) => {
            panel.classList.toggle('is-visible', i === index);
        });

        document.querySelectorAll('.gform-step-node').forEach((node, i) => {
            node.classList.remove('is-active', 'is-done');
            if (i < index) {
                node.classList.add('is-done');
            } else if (i === index) {
                node.classList.add('is-active');
            }
        });

        currentStep = index;
        window.scrollTo({ top: document.getElementById('ppdbUserForm').offsetTop - 80, behavior: 'smooth' });
    }

    function nextGformStep(index) {
        const currentPanel = document.querySelector(`.gform-section-card[data-page-panel="${index}"]`);
        const inputs = currentPanel.querySelectorAll('input, select, textarea');
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
        if (index > 0) {
            showGformStep(index - 1);
        }
    }
</script>
@endsection
