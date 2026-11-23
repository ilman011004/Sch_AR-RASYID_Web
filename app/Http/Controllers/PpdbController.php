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
    public function form(School $school)
    {
        if (! $school->ppdbIsOpen()) {
            return redirect()->route('ppdb', $school)->with('error', 'Pendaftaran sedang ditutup.');
        }

        $schema = $school->getFormSchema();

        return view('site.ppdb-form', [
            'school' => $school,
            'schema' => $schema,
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
        $attributes = [];

        $allFields = [];
        foreach ($schema['pages'] ?? [] as $page) {
            foreach ($page['fields'] ?? [] as $field) {
                $fId = $field['id'];
                $allFields[$fId] = $field;

                $req = ! empty($field['required']) ? 'required' : 'nullable';

                if ($field['type'] === 'file') {
                    $rules[$fId] = $req.'|file|mimes:jpg,jpeg,png,pdf|max:2048';
                } elseif ($field['type'] === 'checkbox') {
                    $rules[$fId] = $req.'|array';
                    $rules[$fId.'.*'] = 'string';
                } elseif ($field['type'] === 'paragraph') {
                    $rules[$fId] = $req.'|string|max:2500';
                } elseif ($field['type'] === 'number') {
                    $rules[$fId] = $req.'|numeric';
                } elseif ($field['type'] === 'date') {
                    $rules[$fId] = $req.'|date';
                } else {
                    $rules[$fId] = $req.'|string|max:255';
                }

                $attributes[$fId] = mb_strtolower($field['label']);
            }
        }

        $validated = $request->validate($rules, [], $attributes);

        // Petakan hasil isian ke data registrasi (core column & custom_fields)
        $registrationData = [];
        $customResponses = [];

        foreach ($allFields as $fId => $field) {
            $val = $validated[$fId] ?? null;
            $mapping = $field['mapping'] ?? null;

            if ($field['type'] === 'file') {
                if ($request->hasFile($fId)) {
                    $filePath = $request->file($fId)->store('ppdb/'.$school->id, 'local');
                    if ($mapping && in_array($mapping, array_keys(Registration::DOCS), true)) {
                        $registrationData[$mapping] = $filePath;
                    }
                    $customResponses[$fId] = [
                        'label' => $field['label'],
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
                        'type' => $field['type'],
                        'value' => is_array($val) ? array_values(array_filter($val)) : $val,
                    ];
                }
            }
        }

        // Nilai fallback untuk core column di tabel registrations jika admin menamai field secara custom
        $studentNameFallback = $registrationData['student_name']
            ?? (collect($customResponses)->first(fn ($c) => str_contains(mb_strtolower($c['label']), 'nama'))['value'] ?? 'Calon Siswa');

        $registrationData['student_name'] = (string) $studentNameFallback;
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
}
