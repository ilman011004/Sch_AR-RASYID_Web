<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Models\School;
use App\Support\AdminContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class RegistrationController extends Controller
{
    public function index(Request $request)
    {
        $items = $this->query($request)->latest()->paginate(25)->withQueryString();

        return view('admin.ppdb.index', [
            'items' => $items,
            'statuses' => Registration::STATUSES,
            'stages' => Registration::STAGES,
            'filter' => $request->query('status'),
            'stageFilter' => $request->query('stage'),
            'q' => $request->query('q'),
        ]);
    }

    /** Khusus super admin: pendaftar semua jenjang dalam satu halaman. */
    public function all(Request $request)
    {
        $schools = School::jenjang()->orderBy('sort')->orderBy('id')->get();
        $items = $this->allQuery($request, $schools->pluck('id')->all())
            ->with('school')->latest()->paginate(25)->withQueryString();

        $year = (int) $request->query('year');

        // Ringkasan per sekolah (mengikuti filter tahun).
        $counts = Registration::query()
            ->whereIn('school_id', $schools->pluck('id'))
            ->when($year > 0, fn ($b) => $b->where('year', $year))
            ->selectRaw('school_id, stage, status, count(*) as n')
            ->groupBy('school_id', 'stage', 'status')
            ->get()
            ->groupBy('school_id');

        $summary = [];
        foreach ($schools as $s) {
            $rows = $counts->get($s->id, collect());
            $inStage = fn (int $stage) => (int) $rows->where('stage', $stage)->where('status', 'diproses')->sum('n');

            $summary[$s->id] = [
                'total' => (int) $rows->sum('n'),
                's1' => $inStage(1),
                's2' => $inStage(2),
                's3' => $inStage(3),
                'diterima' => (int) $rows->where('status', 'diterima')->sum('n'),
                'ditolak' => (int) $rows->where('status', 'ditolak')->sum('n'),
            ];
        }

        return view('admin.ppdb.all', [
            'items' => $items,
            'schools' => $schools,
            'summary' => $summary,
            'years' => Registration::query()->select('year')->distinct()->orderByDesc('year')->pluck('year'),
            'statuses' => Registration::STATUSES,
            'stages' => Registration::STAGES,
            'filters' => [
                'school' => (int) $request->query('school'),
                'year' => $year,
                'stage' => $request->query('stage'),
                'status' => $request->query('status'),
                'q' => $request->query('q'),
            ],
        ]);
    }

    public function show(int $id)
    {
        return view('admin.ppdb.show', [
            'r' => $this->find($id),
            'docs' => Registration::DOCS,
            'stages' => Registration::STAGES,
            'testModes' => Registration::TEST_MODES,
        ]);
    }

    /**
     * Satu pintu untuk semua perubahan; jenisnya ditentukan field "action".
     * note | docs | advance | test | score | interview | finish | back
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $r = $this->find($id);

        $action = $request->validate([
            'action' => ['required', Rule::in(['note', 'docs', 'advance', 'test', 'score', 'interview', 'finish', 'back'])],
        ])['action'];

        return match ($action) {
            'note' => $this->saveNote($request, $r),
            'docs' => $this->saveDocs($request, $r),
            'advance' => $this->advance($r),
            'test' => $this->saveTestSchedule($request, $r),
            'score' => $this->saveScore($request, $r),
            'interview' => $this->saveInterview($request, $r),
            'finish' => $this->finish($request, $r),
            'back' => $this->back($r),
        };
    }

    public function destroy(Request $request, int $id)
    {
        $r = $this->find($id);

        foreach (array_keys(Registration::DOCS) as $field) {
            if ($r->{$field}) {
                Storage::disk('local')->delete($r->{$field});
            }
        }

        $r->delete();

        return redirect()
            ->route($request->user()->isSuperAdmin() ? 'admin.ppdb.all' : 'admin.ppdb.index')
            ->with('status', 'Data pendaftar dihapus.');
    }

    /** Dokumen ada di disk privat; hanya admin sekolah yang bersangkutan (atau super admin) yang bisa membukanya. */
    public function document(int $id, string $field)
    {
        abort_unless(array_key_exists($field, Registration::DOCS), 404);

        $r = $this->find($id);
        $path = $r->{$field};

        abort_unless($path, 404);

        $root = rtrim((string) config('filesystems.disks.local.root'), '/\\');
        $full = $root.DIRECTORY_SEPARATOR.$path;

        abort_unless(is_file($full), 404);

        return response()->file($full, [
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    /** CSV satu sekolah (UTF-8) yang bisa dibuka langsung di Excel. */
    public function export(Request $request)
    {
        $rows = $this->query($request)->orderBy('year')->orderBy('seq')->get();
        $school = AdminContext::school();

        return $this->csv($rows, 'pendaftar-'.$school->slug.'-'.now()->format('Ymd').'.csv', false);
    }

    /** CSV semua jenjang (khusus super admin). */
    public function exportAll(Request $request)
    {
        $ids = School::jenjang()->pluck('id')->all();
        $rows = $this->allQuery($request, $ids)->with('school')
            ->orderBy('school_id')->orderBy('year')->orderBy('seq')->get();

        return $this->csv($rows, 'pendaftar-semua-jenjang-'.now()->format('Ymd').'.csv', true);
    }

    // ------------------------------------------------------------------
    // Aksi tahapan
    // ------------------------------------------------------------------

    private function saveNote(Request $request, Registration $r): RedirectResponse
    {
        $data = $request->validate(['admin_note' => 'nullable|string|max:1000']);
        $r->update($data);

        return back()->with('status', 'Catatan disimpan.');
    }

    private function saveDocs(Request $request, Registration $r): RedirectResponse
    {
        $verified = $request->boolean('docs_verified');

        if ($verified && ! $r->docsComplete()) {
            return back()->withErrors(['docs_verified' => 'Berkas belum lengkap, tidak bisa ditandai lengkap.']);
        }

        $r->update(['docs_verified' => $verified]);

        return back()->with('status', $verified ? 'Berkas ditandai lengkap.' : 'Tanda berkas lengkap dicabut.');
    }

    private function advance(Registration $r): RedirectResponse
    {
        if ($r->status !== 'diproses') {
            return back()->withErrors(['action' => 'Pendaftaran ini sudah memiliki hasil akhir.']);
        }

        if ($r->stage === 1) {
            if (! $r->docs_verified || ! $r->docsComplete()) {
                return back()->withErrors(['action' => 'Centang dulu "Berkas sudah diperiksa dan lengkap".']);
            }

            $r->update(['stage' => 2]);
            $this->notify($r, 'Selamat, Anda Lolos Tahap 1');

            return back()->with('status', 'Lolos Tahap 1. Isi jadwal tes seleksi, lalu beri tahu pendaftar.');
        }

        if ($r->stage === 2) {
            if ($r->test_score === null) {
                return back()->withErrors(['action' => 'Isi nilai tes dulu sebelum lanjut ke wawancara.']);
            }

            $r->update(['stage' => 3]);
            $this->notify($r, 'Selamat, Anda Lolos Tahap 2 (Seleksi)');

            return back()->with('status', 'Lanjut ke Tahap 3 (Wawancara). Isi jadwal wawancara, lalu beri tahu pendaftar.');
        }

        return back()->withErrors(['action' => 'Untuk menyelesaikan, gunakan panel "Hasil akhir".']);
    }

    private function saveTestSchedule(Request $request, Registration $r): RedirectResponse
    {
        if ($r->stage < 2) {
            return back()->withErrors(['action' => 'Jadwal tes baru bisa diisi setelah lolos Tahap 1.']);
        }

        $data = $request->validate([
            'test_at' => 'required|date',
            'test_mode' => ['required', Rule::in(array_keys(Registration::TEST_MODES))],
            'test_place' => 'required|string|max:255',
            'test_info' => 'nullable|string|max:1000',
        ], [], [
            'test_at' => 'tanggal & jam tes',
            'test_mode' => 'cara tes (online/offline)',
            'test_place' => 'tempat / tautan tes',
            'test_info' => 'catatan tes',
        ]);

        $r->update($data);
        $this->notify($r, 'Jadwal Tes Seleksi');

        return back()->with('status', 'Jadwal tes disimpan. Pendaftar diberi tahu lewat email (jika tersedia).');
    }

    private function saveScore(Request $request, Registration $r): RedirectResponse
    {
        if ($r->stage < 2) {
            return back()->withErrors(['action' => 'Nilai tes baru bisa diisi setelah lolos Tahap 1.']);
        }

        $data = $request->validate([
            'test_score' => 'required|numeric|min:0|max:100',
            'test_note' => 'nullable|string|max:1000',
        ], [], ['test_score' => 'nilai tes']);

        $r->update($data);

        return back()->with('status', 'Nilai tes disimpan.');
    }

    private function saveInterview(Request $request, Registration $r): RedirectResponse
    {
        if ($r->stage < 3) {
            return back()->withErrors(['action' => 'Jadwal wawancara baru bisa diisi di Tahap 3.']);
        }

        $data = $request->validate([
            'interview_at' => 'required|date',
            'interview_place' => 'required|string|max:200',
            'interview_note' => 'nullable|string|max:1000',
        ], [], ['interview_at' => 'tanggal & jam wawancara', 'interview_place' => 'tempat wawancara']);

        $r->update($data);
        $this->notify($r, 'Jadwal Wawancara');

        return back()->with('status', 'Jadwal wawancara disimpan. Pendaftar diberi tahu lewat email (jika tersedia).');
    }

    private function finish(Request $request, Registration $r): RedirectResponse
    {
        $result = $request->validate([
            'result' => ['required', Rule::in(['diterima', 'ditolak'])],
        ], [], ['result' => 'hasil akhir'])['result'];

        if ($result === 'diterima' && ($r->stage < 3 || ! $r->interview_at)) {
            return back()->withErrors(['action' => 'Untuk menerima, pendaftar harus sudah di Tahap 3 dan jadwal wawancara terisi.']);
        }

        $r->update(['stage' => 4, 'status' => $result]);
        $this->notify($r, $result === 'diterima' ? 'Selamat, Anda Diterima' : 'Hasil Penerimaan');

        return back()->with('status', 'Hasil akhir disimpan. Pendaftar diberi tahu lewat email (jika tersedia).');
    }

    private function back(Registration $r): RedirectResponse
    {
        if ($r->stage <= 1) {
            return back()->withErrors(['action' => 'Sudah di tahap pertama.']);
        }

        $r->update(['stage' => $r->stage - 1, 'status' => 'diproses']);

        return back()->with('status', 'Dikembalikan ke tahap '.$r->stage.' ('.$r->stageLabel().').');
    }

    // ------------------------------------------------------------------

    /** Email pemberitahuan; isinya sama dengan pesan WhatsApp. Gagal kirim tidak menggagalkan perubahan tahap. */
    private function notify(Registration $r, string $subject): void
    {
        $r = $r->fresh(['school', 'user']);
        $to = $r->parent_email ?: $r->user?->email;

        if (! $to) {
            return;
        }

        $text = "Assalamualaikum, Bapak/Ibu {$r->parent_name}.\n\n"
            .$r->stageMessage()
            ."\n\nPantau status pendaftaran: ".route('ppdb.status', $r->school)
            ."\n\n".$r->school->name;

        try {
            Mail::raw($text, function ($m) use ($to, $subject, $r) {
                $m->to($to)->subject($subject.' — '.$r->school->name);
            });
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /** Pendaftar satu sekolah (sekolah yang sedang dikelola). */
    private function query(Request $request)
    {
        return $this->filtered(Registration::where('school_id', AdminContext::schoolId()), $request);
    }

    /** Pendaftar semua jenjang, bisa dipersempit lewat ?school=ID. */
    private function allQuery(Request $request, array $schoolIds)
    {
        $school = (int) $request->query('school');

        $builder = Registration::query()
            ->whereIn('school_id', $schoolIds)
            ->when($school > 0 && in_array($school, $schoolIds, true), fn ($b) => $b->where('school_id', $school));

        return $this->filtered($builder, $request);
    }

    /** Filter yang dipakai bersama: tahun, status, tahap, dan pencarian. */
    private function filtered($builder, Request $request)
    {
        $status = $request->query('status');
        $stage = (int) $request->query('stage');
        $year = (int) $request->query('year');
        $q = trim((string) $request->query('q'));

        return $builder
            ->when($year > 0, fn ($b) => $b->where('year', $year))
            ->when($status && array_key_exists($status, Registration::STATUSES), fn ($b) => $b->where('status', $status))
            ->when(array_key_exists($stage, Registration::STAGES), fn ($b) => $b->where('stage', $stage))
            ->when($q !== '', function ($b) use ($q) {
                $like = '%'.str_replace(['%', '_'], ['\%', '\_'], mb_strtolower($q)).'%';
                $b->where(function ($w) use ($like) {
                    $w->whereRaw('LOWER(student_name) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(reg_number) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(parent_name) LIKE ?', [$like]);
                });
            });
    }

    /** Admin sekolah hanya bisa membuka pendaftar sekolahnya; super admin bisa membuka semua jenjang. */
    private function find(int $id): Registration
    {
        $builder = Registration::query();

        if (! auth()->user()->isSuperAdmin()) {
            $builder->where('school_id', AdminContext::schoolId());
        }

        return $builder->findOrFail($id);
    }

    private function csv($rows, string $filename, bool $withSchool)
    {
        $headers = array_merge($withSchool ? ['Sekolah'] : [], [
            'No. Pendaftaran', 'Tanggal Daftar', 'Tahap', 'Status', 'Jadwal Tes', 'Cara Tes', 'Nilai Tes', 'Jadwal Wawancara',
            'Nama Calon Siswa', 'NIK', 'L/P', 'Tempat Lahir', 'Tanggal Lahir', 'Agama', 'Alamat', 'Asal Sekolah',
            'Nama Orang Tua/Wali', 'No. HP', 'Email', 'Pekerjaan', 'Catatan',
        ]);

        return response()->streamDownload(function () use ($rows, $headers, $withSchool) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM supaya Excel membaca UTF-8
            fputcsv($out, $headers);

            foreach ($rows as $r) {
                $line = [
                    $r->reg_number, $r->created_at->format('d/m/Y H:i'), $r->stageLabel(), $r->statusLabel(),
                    $r->test_at?->format('d/m/Y H:i'), $r->testModeLabel(), $r->scoreLabel(), $r->interview_at?->format('d/m/Y H:i'),
                    $r->student_name, $r->nik, $r->gender, $r->birth_place, $r->birth_date->format('d/m/Y'),
                    $r->religion, $r->address, $r->previous_school, $r->parent_name, $r->parent_phone,
                    $r->parent_email, $r->parent_job, $r->admin_note,
                ];

                if ($withSchool) {
                    array_unshift($line, $r->school?->name);
                }

                fputcsv($out, array_map([$this, 'safeCell'], $line));
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Mencegah "CSV injection": sel yang diawali = + - @ dibaca Excel sebagai rumus. */
    public function safeCell(mixed $value): string
    {
        $value = (string) $value;

        return $value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true) ? "'".$value : $value;
    }
}