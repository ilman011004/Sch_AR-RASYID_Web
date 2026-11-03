<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PpdbController extends Controller
{
    public function index(School $school)
    {
        return view('site.ppdb', ['school' => $school, 'open' => $school->ppdbIsOpen()]);
    }

    public function store(Request $request, School $school)
    {
        if (! $school->ppdbIsOpen()) {
            return redirect()->route('ppdb', $school)->with('error', 'Pendaftaran sedang ditutup.');
        }

        $data = $request->validate([
            'student_name' => 'required|string|max:150',
            'nik' => 'nullable|digits:16',
            'gender' => 'required|in:L,P',
            'birth_place' => 'required|string|max:100',
            'birth_date' => 'required|date|before:today',
            'religion' => 'nullable|string|max:30',
            'address' => 'required|string|max:500',
            'previous_school' => 'nullable|string|max:150',
            'parent_name' => 'required|string|max:150',
            'parent_phone' => ['required', 'regex:/^[0-9+\-\s]{8,20}$/'],
            'parent_email' => 'nullable|email|max:150',
            'parent_job' => 'nullable|string|max:100',
            'doc_birth_cert' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'doc_family_card' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'doc_photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'website' => 'prohibited', // kolom jebakan bot
        ], [], [
            'student_name' => 'nama calon siswa',
            'birth_place' => 'tempat lahir',
            'birth_date' => 'tanggal lahir',
            'address' => 'alamat',
            'parent_name' => 'nama orang tua/wali',
            'parent_phone' => 'nomor HP orang tua/wali',
            'parent_email' => 'email orang tua/wali',
            'doc_birth_cert' => 'akta kelahiran',
            'doc_family_card' => 'kartu keluarga',
            'doc_photo' => 'pas foto',
        ]);

        unset($data['website']);

        // Dokumen disimpan di disk privat (tidak bisa diakses lewat URL publik).
        foreach (array_keys(Registration::DOCS) as $field) {
            if ($request->hasFile($field)) {
                $data[$field] = $request->file($field)->store('ppdb/'.$school->id, 'local');
            }
        }

        $registration = DB::transaction(function () use ($school, $data) {
            // Kunci baris sekolah agar dua pendaftar bersamaan tidak mendapat nomor yang sama.
            School::whereKey($school->id)->lockForUpdate()->first();

            $year = $school->ppdbYear();
            $seq = (int) Registration::where('school_id', $school->id)->where('year', $year)->max('seq') + 1;

            return Registration::create($data + [
                'school_id' => $school->id,
                'year' => $year,
                'seq' => $seq,
                'reg_number' => sprintf('PPDB-%d-%s-%04d', $year, strtoupper($school->slug), $seq),
                'status' => 'baru',
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

    public function status(School $school)
    {
        return view('site.ppdb-status', ['school' => $school, 'result' => null]);
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
        ]);
    }
}