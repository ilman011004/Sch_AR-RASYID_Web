@php
    $stage = (int) ($r->stage ?: 1);
    $finished = $stage >= 4;
    $isOnline = $r->test_mode === 'online';
@endphp

<div class="result ppdb-track">
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.5rem;margin-bottom:1rem">
        <div>
            <h3 style="margin:0">{{ $r->student_name }}</h3>
            <p class="muted small" style="margin:0">{{ $r->reg_number }}</p>
        </div>
        <div style="display:flex;gap:.5rem;flex-wrap:wrap">
            <a class="btn btn-sm btn-outline" href="{{ route('ppdb.print', [$r->school, $r->reg_number]) }}" target="_blank" style="padding:.4rem .9rem;font-size:.82rem;font-weight:700">
                <span>📄</span> Bukti Pendaftaran
            </a>
            @if ($stage >= 2)
                <a class="btn btn-sm" href="{{ route('ppdb.card', [$r->school, $r->reg_number]) }}" target="_blank" style="padding:.4rem .9rem;font-size:.82rem;font-weight:700;background:#0f766e;border-color:#0f766e">
                    <span>📇</span> Cetak Kartu Ujian Seleksi
                </a>
            @endif
        </div>
    </div>

    <ol class="stepper">
        @foreach (\App\Models\Registration::STAGES as $n => $label)
            @php $isDone = $stage > $n || ($finished && $n === 4); @endphp
            <li @class(['done' => $isDone, 'current' => $stage === $n && ! $finished])>
                <span class="dot">{{ $isDone ? '✓' : $n }}</span>
                <span class="lbl">{{ $label }}</span>
            </li>
        @endforeach
    </ol>

    <p>
        Status:
        <span class="tag {{ $r->status === 'diterima' ? 'tag-ok' : ($r->status === 'ditolak' ? 'tag-warn' : '') }}">{{ $r->statusLabel() }}</span>
    </p>

    @if ($finished)
        <p>
            @if ($r->status === 'diterima')
                Alhamdulillah, selamat! Calon siswa dinyatakan <strong>diterima</strong>. Silakan hubungi sekolah untuk daftar ulang.
            @else
                Mohon maaf, calon siswa belum dapat kami terima pada penerimaan kali ini. Terima kasih telah mendaftar.
            @endif
        </p>
    @elseif ($stage === 1)
        <p>
            @if ($r->docs_verified)
                Berkas Anda sudah dinyatakan lengkap. Menunggu penetapan lolos Tahap 1.
            @else
                Berkas Anda sedang diperiksa panitia.
            @endif
        </p>
    @elseif ($stage === 2)
        <p>Selamat, Anda lolos Tahap 1. Saat ini calon siswa masuk <strong>Tahap 2: Seleksi</strong>.</p>

        @if ($r->test_at)
            <div class="interview-box">
                <strong>Jadwal tes seleksi</strong>
                <div>{{ $r->test_at->translatedFormat('l, d F Y') }}, pukul {{ $r->test_at->format('H:i') }}</div>
                <div>Cara: <strong>{{ $r->testModeLabel() }}</strong></div>
                <div>
                    @if ($isOnline)
                        Tautan/platform:
                        @if (filter_var($r->test_place, FILTER_VALIDATE_URL) && preg_match('#^https?://#i', (string) $r->test_place))
                            <a href="{{ $r->test_place }}" target="_blank" rel="noopener">{{ $r->test_place }}</a>
                        @else
                            {{ $r->test_place }}
                        @endif
                    @else
                        Tempat: {{ $r->test_place }}
                    @endif
                </div>
                @if ($r->test_info)<div class="muted small">{{ $r->test_info }}</div>@endif
            </div>
        @else
            <p class="muted">Jadwal tes seleksi (waktu serta online atau offline) belum ditentukan. Kami akan memberi tahu Anda.</p>
        @endif
    @elseif ($stage === 3)
        <p>Selamat, Anda lolos Tahap 2 (Seleksi) dan masuk <strong>Tahap 3: Wawancara</strong>.</p>

        @if ($r->interview_at)
            <div class="interview-box">
                <strong>Jadwal wawancara</strong>
                <div>{{ $r->interview_at->translatedFormat('l, d F Y') }}, pukul {{ $r->interview_at->format('H:i') }}</div>
                <div>Tempat: {{ $r->interview_place }}</div>
                @if ($r->interview_note)<div class="muted small">{{ $r->interview_note }}</div>@endif
            </div>
        @else
            <p class="muted">Jadwal wawancara belum ditentukan. Kami akan memberi tahu Anda.</p>
        @endif
    @endif

    @if ($r->admin_note)
        <p><strong>Catatan sekolah:</strong><br>{{ $r->admin_note }}</p>
    @endif
</div>