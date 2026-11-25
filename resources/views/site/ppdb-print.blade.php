<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bukti Pendaftaran PPDB — {{ $r->reg_number }} — {{ $r->student_name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
            line-height: 1.5;
            padding: 2rem 1rem;
        }
        .print-actions {
            max-width: 820px;
            margin: 0 auto 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fff;
            padding: 1rem 1.4rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,.06);
            border: 1px solid #e2e8f0;
        }
        .btn-print {
            background: #0f766e;
            color: #fff;
            border: none;
            padding: .65rem 1.4rem;
            border-radius: 8px;
            font-weight: 700;
            font-size: .92rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            text-decoration: none;
            transition: background .15s;
        }
        .btn-print:hover { background: #115e59; color: #fff; }
        .btn-back {
            color: #64748b;
            text-decoration: none;
            font-weight: 600;
            font-size: .88rem;
        }
        .btn-back:hover { color: #0f172a; }

        /* Kertas Formulir Cetak (A4) */
        .sheet {
            max-width: 820px;
            margin: 0 auto;
            background: #fff;
            padding: 2.5rem 3rem;
            border-radius: 14px;
            box-shadow: 0 4px 20px rgba(0,0,0,.08);
            border: 1px solid #cbd5e1;
            position: relative;
        }

        /* Kop Surat Resmi */
        .kop {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            padding-bottom: 1.2rem;
            border-bottom: 3px double #0f172a;
            margin-bottom: 1.5rem;
        }
        .kop-logo {
            width: 75px;
            height: 75px;
            object-fit: contain;
            flex-shrink: 0;
        }
        .kop-text {
            flex: 1;
            text-align: center;
        }
        .kop-yayasan {
            font-size: .95rem;
            font-weight: 700;
            color: #475569;
            letter-spacing: .5px;
            text-transform: uppercase;
        }
        .kop-sekolah {
            font-size: 1.45rem;
            font-weight: 800;
            color: #0f172a;
            margin: .15rem 0;
            text-transform: uppercase;
        }
        .kop-alamat {
            font-size: .8rem;
            color: #64748b;
            line-height: 1.35;
        }

        /* Judul Dokumen */
        .doc-title {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .doc-title h2 {
            font-size: 1.25rem;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: .3px;
            text-transform: uppercase;
        }
        .doc-title .reg-badge {
            display: inline-block;
            margin-top: .4rem;
            padding: .35rem 1rem;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-radius: 999px;
            font-weight: 800;
            font-size: 1.05rem;
            color: #0f766e;
            letter-spacing: 1px;
        }

        /* Status & Tanggal Card */
        .status-box {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: .8rem 1.2rem;
            margin-bottom: 1.8rem;
            font-size: .88rem;
        }
        .status-box b { color: #0f172a; }

        /* Tabel Data Isian */
        .section-header {
            background: #f1f5f9;
            color: #0f172a;
            font-weight: 800;
            font-size: .88rem;
            padding: .5rem .8rem;
            border-radius: 6px;
            margin-top: 1.2rem;
            margin-bottom: .6rem;
            border-left: 4px solid #0f766e;
            text-transform: uppercase;
            letter-spacing: .3px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
            font-size: .9rem;
        }
        .data-table td {
            padding: .55rem .75rem;
            vertical-align: top;
            border-bottom: 1px solid #f1f5f9;
        }
        .data-table td.col-label {
            width: 35%;
            font-weight: 600;
            color: #475569;
        }
        .data-table td.col-sep {
            width: 2%;
            text-align: center;
            color: #94a3b8;
        }
        .data-table td.col-val {
            width: 63%;
            color: #0f172a;
            font-weight: 500;
        }

        /* Bagian Tanda Tangan */
        .ttd-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-top: 2.5rem;
            padding-top: 1rem;
            page-break-inside: avoid;
        }
        .ttd-box {
            text-align: center;
            font-size: .88rem;
        }
        .ttd-space {
            height: 70px;
        }
        .ttd-name {
            font-weight: 800;
            color: #0f172a;
            border-bottom: 1px solid #0f172a;
            display: inline-block;
            min-width: 180px;
            padding-bottom: .2rem;
        }

        /* Catatan Kaki */
        .doc-footer {
            margin-top: 2.5rem;
            padding-top: 1rem;
            border-top: 1px solid #e2e8f0;
            font-size: .75rem;
            color: #64748b;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Aturan Print / Cetak / Unduh PDF */
        @media print {
            body {
                background: #fff;
                padding: 0;
                color: #000;
            }
            .print-actions { display: none !important; }
            .sheet {
                box-shadow: none;
                border: none;
                max-width: 100%;
                padding: 0;
            }
            .doc-title .reg-badge {
                border: 1px solid #000;
                color: #000;
            }
            .section-header {
                background: #f1f5f9 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            @page {
                size: A4 portrait;
                margin: 1.5cm;
            }
        }
    </style>
</head>
<body>

    <div class="print-actions">
        <div>
            <a href="{{ route('ppdb.status', $school) }}" class="btn-back">← Kembali ke Cek Status</a>
        </div>
        <div style="display:flex;gap:.8rem;align-items:center">
            <button onclick="window.print()" class="btn-print">
                <span>🖨️</span> Cetak / Simpan PDF
            </button>
        </div>
    </div>

    <div class="sheet">
        {{-- Kop Surat Resmi --}}
        <div class="kop">
            @if ($school->logoUrl())
                <img src="{{ $school->logoUrl() }}" alt="Logo" class="kop-logo">
            @endif
            <div class="kop-text">
                <div class="kop-yayasan">Yayasan Pendidikan dan Pengembangan Insani (YPPI) Ar-Rasyid</div>
                <h1 class="kop-sekolah">{{ $school->name }}</h1>
                <div class="kop-alamat">
                    {{ $school->address ?: 'Segumbang, Kalimantan Selatan' }}<br>
                    @if ($school->phone) Telp/WA: {{ $school->phone }} @endif
                    @if ($school->email) · Email: {{ $school->email }} @endif
                </div>
            </div>
        </div>

        {{-- Judul Bukti Pendaftaran --}}
        <div class="doc-title">
            <h2>Bukti Pendaftaran Siswa Baru (PPDB)</h2>
            <div class="reg-badge">{{ $r->reg_number }}</div>
        </div>

        {{-- Info Waktu & Status --}}
        <div class="status-box">
            <div><b>Tahun Ajaran:</b> {{ $r->year }}/{{ $r->year + 1 }}</div>
            <div><b>Tanggal Daftar:</b> {{ $r->created_at->translatedFormat('d F Y, H:i') }} WITA</div>
            <div><b>Status Awal:</b> {{ $r->statusLabel() }}</div>
        </div>

        {{-- Data Isian Pendaftaran --}}
        @php $prevSec = null; @endphp
        @foreach ($items as $it)
            @if (! empty($it['section']) && $it['section'] !== $prevSec)
                @if ($prevSec !== null) </table> @endif
                <div class="section-header">{{ $it['section'] }}</div>
                <table class="data-table">
            @elseif ($loop->first)
                <div class="section-header">Biodata & Informasi Pendaftaran</div>
                <table class="data-table">
            @endif
            @php
                $prevSec = $it['section'] ?? null;
                $v = $it['value'];
                $text = is_array($v) ? implode(', ', $v) : (string) $v;
            @endphp
            <tr>
                <td class="col-label">{{ $it['label'] }}</td>
                <td class="col-sep">:</td>
                <td class="col-val">
                    @if ($it['type'] === 'file')
                        <span style="color:#0f766e;font-weight:600">✓ Dokumen Terlampir di Sistem</span>
                    @elseif (filled($v))
                        {!! nl2br(e($text)) !!}
                    @else
                        -
                    @endif
                </td>
            </tr>
            @if ($loop->last) </table> @endif
        @endforeach

        {{-- Lembar Tanda Tangan --}}
        <div class="ttd-grid">
            <div class="ttd-box">
                <p>Orang Tua / Wali Calon Siswa,</p>
                <div class="ttd-space"></div>
                <div class="ttd-name">{{ $r->parent_name ?: '( ........................................ )' }}</div>
            </div>
            @php
                // Ambil nama tempat/kota dari alamat sekolah yang diatur di admin
                $lokasiTtd = trim(explode(',', $school->address ?? '')[0] ?? '');
                if (empty($lokasiTtd)) {
                    $lokasiTtd = 'Segumbang';
                }
            @endphp
            <div class="ttd-box">
                <p>{{ $lokasiTtd }}, {{ now()->translatedFormat('d F Y') }}<br>Panitia PPDB {{ $school->name }},</p>
                <div class="ttd-space"></div>
                <div class="ttd-name">( Panitia Penerimaan )</div>
            </div>
        </div>

        {{-- Catatan Kaki --}}
        <div class="doc-footer">
            <span>Lembar ini adalah bukti pendaftaran sah yang dicetak secara elektronik melalui sistem PPDB Online.</span>
            <span>ID: #{{ $r->id }}</span>
        </div>
    </div>

</body>
</html>
