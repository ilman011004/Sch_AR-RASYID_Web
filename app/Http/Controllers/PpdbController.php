<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PpdbController extends Controller
{
    /** Halaman informasi + poster (publik). */
    public function index(School $school)
    {
        return view('site.ppdb', ['school' => $school, 'open' => $school->ppdbIsOpen()]);
    }

    /** Halaman formulir (wajib login). */
    public function form(Request $request, School $school)
    {
        if (! $school->ppdbIsOpen()) {
            return redirect()->route('ppdb', $school)->with('error', 'Pendaftaran sedang ditutup.');
        }

        $myRegistrations = $this->myRegistrations($request, $school);
        $schema = $school->getFormSchema();

        return view('site.ppdb-form', [
            'school' => $school,
            'schema' => $schema,
            'myRegistrations' => $myRegistrations,
        ]);
    }

    public function store(Request $request, School $school)
    {
        if (! $school->ppdbIsOpen()) {
            return redirect()->route('ppdb', $school)->with('error', 'Pendaftaran sedang ditutup.');
        }

        $schema = $school->getFormSchema();

        $rules = [
            'website' => 'prohibited', // kolom jebakan bot
        ];
        $messages = [];
        $attributes = [];
        $normalize = []; // id pertanyaan => format (nik/phone/digits), dirapikan sebelum divalidasi

        $allFields = [];
        foreach ($schema['pages'] ?? [] as $page) {
            // Judul bagian aktif. Dicatat pada tiap jawaban supaya "Nama Lengkap" milik Ayah dan Ibu tidak tertukar di tampilan admin.
            $section = null;

            foreach ($page['fields'] ?? [] as $field) {
                if (($field['type'] ?? '') === 'section') {
                    $section = $field['label'] ?? null;

                    continue;
                }

                $fId = $field['id'];
                $field['_section'] = $section;
                $allFields[$fId] = $field;

                $req = ! empty($field['required']) ? 'required' : 'nullable';
                $messages[$fId.'.required'] = ':attribute wajib diisi.';

                if ($field['type'] === 'file') {
                    $rules[$fId] = $req.'|file|mimes:jpg,jpeg,png,pdf|max:2048';
                    $messages[$fId.'.mimes'] = ':attribute harus berupa file JPG, PNG, atau PDF.';
                    $messages[$fId.'.max'] = 'Ukuran :attribute maksimal 2 MB.';
                } elseif ($field['type'] === 'checkbox') {
                    $rules[$fId] = $req.'|array';
                    $rules[$fId.'.*'] = 'string';
                } elseif ($field['type'] === 'region') {
                    // Alamat bertingkat: Provinsi, Kabupaten/Kota, Kecamatan, Desa/Kelurahan + nama jalan.
                    $rules[$fId] = $req.'|array';
                    foreach (['provinsi', 'kabupaten', 'kecamatan', 'desa'] as $part) {
                        $rules[$fId.'.'.$part] = $req.'|string|max:120';
                        $attributes[$fId.'.'.$part] = $part === 'desa' ? 'desa/kelurahan' : $part;
                        $messages[$fId.'.'.$part.'.required'] = ':attribute wajib diisi.';
                    }
                    $rules[$fId.'.detail'] = $req.'|string|max:255';
                    $attributes[$fId.'.detail'] = 'nama jalan / RT / RW';
                    $messages[$fId.'.detail.required'] = ':attribute wajib diisi.';
                } elseif ($field['type'] === 'paragraph') {
                    $rules[$fId] = $req.'|string|max:2500';
                } elseif ($field['type'] === 'number') {
                    $format = $this->resolveFormat($field);
                    if ($format === 'nik') {
                        $normalize[$fId] = 'nik';
                        $rules[$fId] = [$req, 'digits:16'];
                        $messages[$fId.'.digits'] = ':attribute harus tepat 16 digit angka (tidak boleh kurang atau lebih).';
                    } elseif ($format === 'phone') {
                        $normalize[$fId] = 'phone';
                        $rules[$fId] = [$req, 'regex:/^08[0-9]{8,12}$/'];
                        $messages[$fId.'.regex'] = ':attribute harus berupa nomor HP Indonesia yang valid (10 sampai 14 digit angka).';
                    } else {
                        $rules[$fId] = $req.'|numeric';
                        $messages[$fId.'.numeric'] = ':attribute harus berupa angka.';
                    }
                } elseif ($field['type'] === 'date') {
                    $rules[$fId] = $req.'|date';
                    $messages[$fId.'.date'] = 'Format :attribute tidak valid.';
                } else {
                    // Jawaban singkat (teks): bisa punya aturan format, panjang minimal dan maksimal.
                    $format = $this->resolveFormat($field);
                    if (in_array($format, ['nik', 'phone', 'digits'], true)) {
                        $normalize[$fId] = $format;
                    }

                    [$extra, $msg] = $this->textRules($field, $format);
                    $rules[$fId] = array_merge([$req, 'string'], $extra);
                    $messages += $msg;
                }

                $attributes[$fId] = mb_strtolower($field['label']);
            }
        }

        // Rapikan NIK / nomor HP sebelum divalidasi: buang spasi, strip, titik, dan ubah +62 / 62 menjadi 0.
        foreach ($normalize as $fId => $format) {
            $v = $request->input($fId);
            if (! is_string($v)) {
                continue;
            }

            $v = preg_replace('/[\s\-().]/', '', trim($v));
            if ($format === 'phone') {
                $v = preg_replace('/^(\+62|62)/', '0', $v);
            }

            $request->merge([$fId => $v]);
        }

        $validated = $request->validate($rules, $messages, $attributes);

        // Petakan hasil isian ke data registrasi (core column & custom_fields)
        $registrationData = [];
        $customResponses = [];

        foreach ($allFields as $fId => $field) {
            $val = $validated[$fId] ?? null;
            $mapping = $field['mapping'] ?? null;
            $parts = null;

            if ($field['type'] === 'region' && is_array($val)) {
                $parts = $this->cleanRegionParts($val);
                $val = $this->composeAddress($parts);
            }

            if ($field['type'] === 'file') {
                if ($request->hasFile($fId)) {
                    $filePath = $request->file($fId)->store('ppdb/'.$school->id, 'local');
                    if ($mapping && in_array($mapping, array_keys(Registration::DOCS), true)) {
                        $registrationData[$mapping] = $filePath;
                    }
                    $customResponses[$fId] = [
                        'label' => $field['label'],
                        'section' => $field['_section'],
                        'type' => 'file',
                        'value' => $filePath,
                    ];
                }
            } else {
                if ($val !== null && $val !== '') {
                    if ($mapping && in_array($mapping, [
                        'student_name', 'nik', 'gender', 'birth_place', 'birth_date', 'religion',
                        'address', 'previous_school', 'parent_name', 'parent_phone', 'parent_email',
                        'parent_job',
                    ], true)) {
                        $registrationData[$mapping] = $val;
                    }

                    $customResponses[$fId] = [
                        'label' => $field['label'],
                        'section' => $field['_section'],
                        'type' => $field['type'],
                        'value' => is_array($val) ? array_values(array_filter($val)) : $val,
                    ] + ($parts ? ['parts' => $parts] : []);
                }
            }
        }

        // Kolom gender di database hanya 1 huruf (L/P), sedangkan formulir memakai pilihan "Laki-laki" / "Perempuan".
        if (isset($registrationData['gender'])) {
            $registrationData['gender'] = str_starts_with(mb_strtolower((string) $registrationData['gender']), 'p') ? 'P' : 'L';
        }

        // Nilai fallback untuk core column di tabel registrations jika admin menamai field secara custom
        $studentNameFallback = $registrationData['student_name']
            ?? (collect($customResponses)->first(fn ($c) => str_contains(mb_strtolower($c['label']), 'nama'))['value'] ?? 'Calon Siswa');

        $prevSchoolFallback = $registrationData['previous_school']
            ?? (collect($customResponses)->first(fn ($c) => (str_contains(mb_strtolower($c['label']), 'asal sekolah') || (str_contains(mb_strtolower($c['label']), 'nama sekolah') && str_contains(mb_strtolower($c['section'] ?? ''), 'pendidikan'))))['value'] ?? null);

        $registrationData['student_name'] = (string) $studentNameFallback;
        $registrationData['previous_school'] = $prevSchoolFallback ? (string) $prevSchoolFallback : null;
        $registrationData['gender'] = $registrationData['gender'] ?? 'L';
        $registrationData['birth_place'] = $registrationData['birth_place'] ?? '-';
        $registrationData['birth_date'] = $registrationData['birth_date'] ?? now()->subYears(7)->toDateString();
        $registrationData['address'] = $registrationData['address'] ?? '-';
        $registrationData['parent_name'] = $registrationData['parent_name'] ?? $request->user()->name;
        $registrationData['parent_phone'] = $registrationData['parent_phone'] ?? '-';

        $registrationData['custom_fields'] = ! empty($customResponses) ? $customResponses : null;

        $userId = $request->user()->id;

        $registration = DB::transaction(function () use ($school, $registrationData, $userId) {
            // Kunci baris sekolah agar nomor pendaftaran unik & berurutan
            School::whereKey($school->id)->lockForUpdate()->first();

            $year = $school->ppdbYear();
            $seq = (int) Registration::where('school_id', $school->id)->where('year', $year)->max('seq') + 1;

            return Registration::create($registrationData + [
                'school_id' => $school->id,
                'user_id' => $userId,
                'year' => $year,
                'seq' => $seq,
                'reg_number' => sprintf('PPDB-%d-%s-%04d', $year, strtoupper($school->slug), $seq),
                'stage' => 1,
                'status' => 'diproses',
            ]);
        });

        return redirect()->route('ppdb.success', $school)->with('reg', [
            'number' => $registration->reg_number,
            'name' => $registration->student_name,
        ]);
    }

    public function success(School $school)
    {
        $reg = session('reg');

        if (! $reg) {
            return redirect()->route('ppdb', $school);
        }

        return view('site.ppdb-success', ['school' => $school, 'reg' => $reg]);
    }

    /**
     * Halaman cetak / simpan PDF bukti pendaftaran PPDB.
     */
    public function printProof(Request $request, School $school, Registration $registration)
    {
        $user = $request->user();

        // Pastikan pendaftaran sesuai dengan sekolah
        if ($registration->school_id !== $school->id) {
            abort(404);
        }

        // Jika user login: izinkan jika pemilik data, admin, atau super admin
        if ($user) {
            if (! $user->isAdmin() && ! $user->isSuperAdmin() && $registration->user_id !== $user->id) {
                abort(403, 'Anda tidak memiliki hak akses untuk bukti pendaftaran ini.');
            }
        } else {
            // Jika belum login (misal buka link langsung dari session selesai pendaftaran atau cek status)
            $regSession = session('reg');
            $sessionMatched = is_array($regSession) && ($regSession['number'] ?? '') === $registration->reg_number;
            if (! $sessionMatched && $registration->user_id !== null) {
                return redirect()->route('ppdb.login', $school)
                    ->with('status', 'Silakan masuk terlebih dahulu untuk mengunduh bukti pendaftaran.');
            }
        }

        // Susun isian formulir untuk dicetak
        $items = [];
        if (! empty($registration->custom_fields) && is_array($registration->custom_fields)) {
            foreach ($registration->custom_fields as $cf) {
                $type = $cf['type'] ?? 'text';
                $value = $cf['value'] ?? null;
                $items[] = [
                    'section' => $cf['section'] ?? null,
                    'label' => $cf['label'] ?? 'Pertanyaan',
                    'type' => $type,
                    'value' => $value,
                ];
            }
        } else {
            $legacy = [
                ['Data calon siswa', 'Nama Lengkap Siswa', $registration->student_name],
                ['Data calon siswa', 'NIK', $registration->nik],
                ['Data calon siswa', 'Jenis Kelamin', $registration->gender === 'L' ? 'Laki-laki' : 'Perempuan'],
                ['Data calon siswa', 'Tempat, Tanggal Lahir', $registration->birth_place.', '.optional($registration->birth_date)->format('d/m/Y')],
                ['Data calon siswa', 'Agama', $registration->religion],
                ['Data calon siswa', 'Alamat', $registration->address],
                ['Data calon siswa', 'Asal Sekolah', $registration->previous_school],
                ['Data Orang Tua / Wali', 'Nama Orang Tua / Wali', $registration->parent_name],
                ['Data Orang Tua / Wali', 'Nomor HP / WhatsApp', $registration->parent_phone],
                ['Data Orang Tua / Wali', 'Email', $registration->parent_email],
                ['Data Orang Tua / Wali', 'Pekerjaan', $registration->parent_job],
            ];
            foreach ($legacy as [$sec, $label, $value]) {
                $items[] = ['section' => $sec, 'label' => $label, 'type' => 'text', 'value' => $value];
            }
        }

        return view('site.ppdb-print', [
            'school' => $school,
            'r' => $registration,
            'items' => $items,
        ]);
    }

    /**
     * Halaman cetak / simpan PDF Kartu Bukti Ujian / Tes Seleksi PPDB.
     */
    public function printExamCard(Request $request, School $school, Registration $registration)
    {
        $user = $request->user();

        if ($registration->school_id !== $school->id) {
            abort(404);
        }

        if ($user) {
            if (! $user->isAdmin() && ! $user->isSuperAdmin() && $registration->user_id !== $user->id) {
                abort(403, 'Anda tidak memiliki hak akses untuk kartu ujian ini.');
            }
        } else {
            $regSession = session('reg');
            $sessionMatched = is_array($regSession) && ($regSession['number'] ?? '') === $registration->reg_number;
            if (! $sessionMatched && $registration->user_id !== null) {
                return redirect()->route('ppdb.login', $school)
                    ->with('status', 'Silakan masuk terlebih dahulu untuk mengunduh kartu peserta tes seleksi.');
            }
        }

        return view('site.ppdb-exam-card', [
            'school' => $school,
            'r' => $registration,
        ]);
    }

    public function status(Request $request, School $school)
    {
        return view('site.ppdb-status', [
            'school' => $school,
            'result' => null,
            'mine' => $this->myRegistrations($request, $school),
        ]);
    }

    public function check(Request $request, School $school)
    {
        $data = $request->validate([
            'reg_number' => 'required|string|max:50',
            'birth_date' => 'required|date',
        ]);

        // Nomor pendaftaran + tanggal lahir harus cocok, supaya data orang lain tidak bisa ditebak.
        $result = Registration::where('school_id', $school->id)
            ->where('reg_number', strtoupper(trim($data['reg_number'])))
            ->whereDate('birth_date', $data['birth_date'])
            ->first();

        return view('site.ppdb-status', [
            'school' => $school,
            'result' => $result,
            'searched' => true,
            'mine' => $this->myRegistrations($request, $school),
        ]);
    }

    /** Pendaftaran milik akun orang tua yang sedang login (kosong bila tamu/admin). */
    private function myRegistrations(Request $request, School $school)
    {
        $user = $request->user();

        if (! $user || ! $user->isParent()) {
            return collect();
        }

        return Registration::where('school_id', $school->id)
            ->where('user_id', $user->id)
            ->latest()
            ->get();
    }

    /**
     * Format aturan isian sebuah pertanyaan teks.
     * Jika admin belum memilih, deteksi otomatis berdasarkan mapping atau label pertanyaan (NIK, No. HP/WA, Email).
     */
    private function resolveFormat(array $field): string
    {
        $format = $field['format'] ?? '';

        if ($format !== '') {
            return $format;
        }

        $mapping = $field['mapping'] ?? null;
        if ($mapping === 'nik') {
            return 'nik';
        }
        if ($mapping === 'parent_phone') {
            return 'phone';
        }
        if ($mapping === 'parent_email') {
            return 'email';
        }

        $label = mb_strtolower(trim($field['label'] ?? ''));
        if (preg_match('/\bnik\b/u', $label)) {
            return 'nik';
        }
        if (preg_match('/(nomor|no\.?)\s*(hp|wa|whatsapp|ponsel|telepon|handphone)|(\bhp\b|\bwa\b|\bwhatsapp\b)/u', $label)) {
            return 'phone';
        }
        if (preg_match('/\b(email|surel)\b/u', $label)) {
            return 'email';
        }

        return '';
    }

    /** Aturan validasi untuk pertanyaan teks singkat. Mengembalikan [daftar aturan, pesan error]. */
    private function textRules(array $field, string $format): array
    {
        $id = $field['id'];
        $min = ! empty($field['min']) ? (int) $field['min'] : null;
        $max = ! empty($field['max']) ? min((int) $field['max'], 255) : 255;
        $rules = [];
        $msg = [];

        switch ($format) {
            case 'nik':
                $rules[] = 'digits:16';
                $msg["$id.digits"] = ':attribute harus tepat 16 digit angka (tidak boleh kurang atau lebih).';
                break;

            case 'phone':
                // 08xxxxxxxxxx (total 10-14 digit). Awalan +62 / 62 sudah diubah menjadi 0 sebelum validasi.
                $rules[] = 'regex:/^08[0-9]{8,12}$/';
                $msg["$id.regex"] = ':attribute harus berupa nomor HP Indonesia yang valid (10 sampai 14 digit angka).';
                break;

            case 'digits':
                $rules[] = 'regex:/^[0-9]+$/';
                $msg["$id.regex"] = ':attribute hanya boleh berisi angka.';
                break;

            case 'email':
                $rules[] = 'email';
                $msg["$id.email"] = 'Format :attribute tidak valid.';
                break;
        }

        // NIK sudah dikunci tepat 16 angka, jadi min/max tidak dipakai.
        if ($format !== 'nik') {
            if ($min) {
                $rules[] = 'min:'.$min;
                $msg["$id.min"] = ':attribute minimal :min karakter.';
            }
            $rules[] = 'max:'.$max;
            $msg["$id.max"] = ':attribute maksimal :max karakter.';
        }

        return [$rules, $msg];
    }

    /** Rapikan bagian alamat bertingkat: hanya kunci yang dikenal, spasi ganda dibuang, bagian kosong dihapus. */
    private function cleanRegionParts(array $val): array
    {
        $parts = [];

        foreach (['detail', 'desa', 'kecamatan', 'kabupaten', 'provinsi'] as $key) {
            $text = trim((string) preg_replace('/\s+/u', ' ', (string) ($val[$key] ?? '')));
            if ($text !== '') {
                $parts[$key] = $text;
            }
        }

        return $parts;
    }

    /** Contoh: "Jl. Mawar No. 5 RT 02, Desa Segumbang, Kecamatan Batu Licin, Kabupaten Tanah Bumbu, Kalimantan Selatan". */
    private function composeAddress(array $parts): string
    {
        if (isset($parts['kecamatan'])) {
            $parts['kecamatan'] = 'Kecamatan '.$parts['kecamatan'];
        }

        return implode(', ', array_filter([
            $parts['detail'] ?? null,
            $parts['desa'] ?? null,
            $parts['kecamatan'] ?? null,
            $parts['kabupaten'] ?? null,
            $parts['provinsi'] ?? null,
        ]));
    }
}