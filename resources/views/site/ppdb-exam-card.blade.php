<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kartu Peserta Tes Seleksi PPDB — {{ $r->reg_number }} — {{ $r->student_name }}</title>
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
            max-width: 760px;
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
        }
        .btn-print:hover { background: #115e59; color: #fff; }
        .btn-back {
            color: #64748b;
            text-decoration: none;
            font-weight: 600;
            font-size: .88rem;
        }
        .btn-back:hover { color: #0f172a; }

        /* Kartu Peserta Ujian / Tes Seleksi */
        .card-sheet {
            max-width: 760px;
            margin: 0 auto;
            background: #fff;
            padding: 2.2rem 2.6rem;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,.08);
            border: 2px solid #0f766e;
            position: relative;
        }

        /* Kop Kartu */
        .kop {
            display: flex;
            align-items: center;
            gap: 1.2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #0f766e;
            margin-bottom: 1.2rem;
        }
        .kop-logo {
            width: 65px;
            height: 65px;
            object-fit: contain;
            flex-shrink: 0;
        }
        .kop-text { flex: 1; text-align: center; }
        .kop-yayasan {
            font-size: .85rem;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
        }
        .kop-sekolah {
            font-size: 1.35rem;
            font-weight: 800;
            color: #0f172a;
            margin: .1rem 0;
            text-transform: uppercase;
        }
        .kop-alamat { font-size: .78rem; color: #64748b; }

        /* Badge Kartu */
        .card-title-box {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .card-title-badge {
            display: inline-block;
            background: #0f766e;
            color: #fff;
            padding: .35rem 1.4rem;
            border-radius: 999px;
            font-weight: 800;
            font-size: 1rem;
            letter-spacing: .5px;
            text-transform: uppercase;
        }
        .card-subtitle {
            margin-top: .4rem;
            font-size: .85rem;
            color: #64748b;
            font-weight: 600;
        }

        /* Layout Data & Foto */
        .card-body {
            display: grid;
            grid-template-columns: 1fr 140px;
            gap: 1.8rem;
            align-items: start;
            margin-bottom: 1.5rem;
        }
        @media (max-width: 600px) {
            .card-body { grid-template-columns: 1fr; }
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            font-size: .9rem;
        }
        .info-table td {
            padding: .45rem 0;
            vertical-align: top;
        }
        .info-table td.lbl {
            width: 38%;
            font-weight: 600;
            color: #475569;
        }
        .info-table td.sep {
            width: 3%;
            color: #94a3b8;
        }
        .info-table td.val {
            width: 59%;
            font-weight: 700;
            color: #0f172a;
        }

        /* Kotak Pas Foto 3x4 */
        .photo-box {
            width: 130px;
            height: 170px;
            border: 2px dashed #cbd5e1;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: #f8fafc;
            color: #94a3b8;
            font-size: .78rem;
            text-align: center;
            padding: .5rem;
            overflow: hidden;
            margin-inline: auto;
        }
        .photo-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 6px;
        }

        /* Kotak Jadwal Tes */
        .schedule-box {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 12px;
            padding: 1.1rem 1.4rem;
            margin-bottom: 1.5rem;
        }
        .schedule-box h4 {
            font-size: .92rem;
            font-weight: 800;
            color: #166534;
            margin-bottom: .6rem;
            display: flex;
            align-items: center;
            gap: .4rem;
        }
        .schedule-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .8rem;
            font-size: .88rem;
        }
        @media (max-width: 600px) {
            .schedule-grid { grid-template-columns: 1fr; }
        }
        .schedule-item b {
            display: block;
            color: #475569;
            font-size: .76rem;
            text-transform: uppercase;
        }
        .schedule-item span {
            color: #0f172a;
            font-weight: 700;
        }

        /* Tata Tertib / Petunjuk Tes */
        .rules-box {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: .9rem 1.2rem;
            background: #fff;
            font-size: .8rem;
            color: #475569;
            margin-bottom: 1.5rem;
        }
        .rules-box b { color: #0f172a; display: block; margin-bottom: .35rem; }
        .rules-box ol { padding-left: 1.2rem; }
        .rules-box li { margin-bottom: .2rem; }

        /* Tanda Tangan */
        .ttd-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 1.5rem;
            font-size: .85rem;
            text-align: center;
        }
        .ttd-col { width: 45%; }
        .ttd-space { height: 60px; }
        .ttd-line {
            font-weight: 800;
            color: #0f172a;
            border-bottom: 1px solid #0f172a;
            display: inline-block;
            min-width: 170px;
            padding-bottom: .2rem;
        }

        @media print {
            body { background: #fff; padding: 0; color: #000; }
            .print-actions { display: none !important; }
            .card-sheet {
                box-shadow: none;
                border: 2px solid #000;
                max-width: 100%;
                padding: 1.5rem;
            }
            .card-title-badge {
                background: #000 !important;
                color: #fff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .schedule-box {
                background: #f8fafc !important;
                border: 1px solid #000;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            @page {
                size: A4 portrait;
                margin: 1.2cm;
            }
        }
    </style>
</head>
<body>

    <div class="print-actions">
        <div>
            <a href="{{ route('ppdb.status', $school) }}" class="btn-back">← Kembali ke Cek Status</a>
        </div>
        <div>
            <button onclick="window.print()" class="btn-print">
                <span>🖨️</span> Cetak / Simpan Kartu Ujian
            </button>
        </div>
    </div>

    <div class="card-sheet">
        {{-- Kop Resmi --}}
        <div class="kop">
            @if ($school->logoUrl())
                <img src="{{ $school->logoUrl() }}" alt="Logo" class="kop-logo">
            @endif
            <div class="kop-text">
                <div class="kop-yayasan">Yayasan Pendidikan dan Pengembangan Insani (YPPI) Ar-Rasyid</div>
                <h1 class="kop-sekolah">{{ $school->name }}</h1>
                <div class="kop-alamat">
                    {{ $school->address ?: 'Segumbang, Kalimantan Selatan' }}
                    @if ($school->phone) · Telp/WA: {{ $school->phone }} @endif
                </div>
            </div>
        </div>

        {{-- Judul Kartu --}}
        <div class="card-title-box">
            <div class="card-title-badge">KARTU PESERTA TES SELEKSI MASUK</div>
            <p class="card-subtitle">Penerimaan Peserta Didik Baru (PPDB) Tahun Ajaran {{ $r->year }}/{{ $r->year + 1 }}</p>
        </div>

        {{-- Biodata & Foto --}}
        <div class="card-body">
            <table class="info-table">
                <tr>
                    <td class="lbl">No. Peserta / Registrasi</td>
                    <td class="sep">:</td>
                    <td class="val" style="color:#0f766e;font-size:1.05rem">{{ $r->reg_number }}</td>
                </tr>
                <tr>
                    <td class="lbl">Nama Lengkap Siswa</td>
                    <td class="sep">:</td>
                    <td class="val">{{ $r->student_name }}</td>
                </tr>
                <tr>
                    <td class="lbl">NIK Siswa</td>
                    <td class="sep">:</td>
                    <td class="val">{{ $r->getNik() ?: '-' }}</td>
                </tr>
                <tr>
                    <td class="lbl">Jenis Kelamin</td>
                    <td class="sep">:</td>
                    <td class="val">{{ $r->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                </tr>
                <tr>
                    <td class="lbl">Asal Sekolah</td>
                    <td class="sep">:</td>
                    <td class="val">{{ $r->getPreviousSchool() ?: '-' }}</td>
                </tr>
                <tr>
                    <td class="lbl">Nama Orang Tua / Wali</td>
                    <td class="sep">:</td>
                    <td class="val">{{ $r->parent_name ?: '-' }}</td>
                </tr>
                <tr>
                    <td class="lbl">Kontak HP / WhatsApp</td>
                    <td class="sep">:</td>
                    <td class="val">{{ $r->parent_phone ?: '-' }}</td>
                </tr>
            </table>

            {{-- Tempat Pas Foto 3x4 --}}
            <div class="photo-box">
                @php $foto = $r->photoPath(); @endphp
                @if ($foto)
                    <img src="{{ asset('storage/'.$foto) }}" alt="Foto Siswa">
                @else
                    <span>PAS FOTO<br>3 x 4</span>
                @endif
            </div>
        </div>

        {{-- Jadwal & Lokasi Ujian / Tes --}}
        <div class="schedule-box">
            <h4>📅 Jadwal & Lokasi Pelaksanaan Tes Seleksi</h4>
            <div class="schedule-grid">
                <div class="schedule-item">
                    <b>Hari & Tanggal Ujian</b>
                    <span>{{ $r->test_at ? $r->test_at->translatedFormat('l, d F Y') : 'Akan dijadwalkan oleh panitia' }}</span>
                </div>
                <div class="schedule-item">
                    <b>Waktu Ujian</b>
                    <span>{{ $r->test_at ? $r->test_at->format('H:i').' WITA' : '-' }}</span>
                </div>
                <div class="schedule-item">
                    <b>Metode Pelaksanaan</b>
                    <span>{{ $r->testModeLabel() ?: 'Offline (Tatap Muka di Sekolah)' }}</span>
                </div>
                <div class="schedule-item">
                    <b>Ruangan / Tautan Pelaksanaan</b>
                    <span>{{ $r->test_place ?: 'Kampus '.$school->name.' (Segumbang)' }}</span>
                </div>
            </div>
            @if ($r->test_info)
                <div style="margin-top:.7rem;padding-top:.5rem;border-top:1px dashed #86efac;font-size:.82rem;color:#166534">
                    <strong>Catatan Khusus:</strong> {{ $r->test_info }}
                </div>
            @endif
        </div>

        {{-- Tata Tertib Ringkas --}}
        <div class="rules-box">
            <b>Petunjuk & Tata Tertib Peserta Tes:</b>
            <ol>
                <li>Peserta wajib membawa <strong>Kartu Peserta Tes ini</strong> dalam bentuk cetak saat pelaksanaan seleksi.</li>
                <li>Hadir di lokasi ujian paling lambat 15 menit sebelum waktu pelaksanaan dimulai.</li>
                <li>Mengenakan pakaian seragam sekolah asal yang rapi, bersih, dan sopan (serta bersepatu).</li>
                <li>Membawa alat tulis lengkap masing-masing.</li>
            </ol>
        </div>

        {{-- Tanda Tangan --}}
        @php
            $lokasiTtd = trim(explode(',', $school->address ?? '')[0] ?? '');
            if (empty($lokasiTtd)) { $lokasiTtd = 'Segumbang'; }
        @endphp
        <div class="ttd-row">
            <div class="ttd-col">
                <p>Calon Peserta Didik Baru,</p>
                <div class="ttd-space"></div>
                <div class="ttd-line">{{ $r->student_name }}</div>
            </div>
            <div class="ttd-col">
                <p>{{ $lokasiTtd }}, {{ now()->translatedFormat('d F Y') }}<br>Panitia PPDB {{ $school->name }},</p>
                <div class="ttd-space"></div>
                <div class="ttd-line">( Panitia Seleksi )</div>
            </div>
        </div>
    </div>

</body>
</html>
