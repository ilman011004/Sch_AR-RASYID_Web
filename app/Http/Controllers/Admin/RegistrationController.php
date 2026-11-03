<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Support\AdminContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class RegistrationController extends Controller
{
    public function index(Request $request)
    {
        $items = $this->query($request)->latest()->paginate(25)->withQueryString();

        return view('admin.ppdb.index', [
            'items' => $items,
            'statuses' => config('school.ppdb_statuses'),
            'filter' => $request->query('status'),
            'q' => $request->query('q'),
        ]);
    }

    public function show(int $id)
    {
        return view('admin.ppdb.show', [
            'r' => $this->find($id),
            'statuses' => config('school.ppdb_statuses'),
            'docs' => Registration::DOCS,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $r = $this->find($id);

        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(config('school.ppdb_statuses')))],
            'admin_note' => 'nullable|string|max:1000',
        ]);

        $r->update($data);

        return back()->with('status', 'Status pendaftaran diperbarui.');
    }

    public function destroy(int $id)
    {
        $r = $this->find($id);

        foreach (array_keys(Registration::DOCS) as $field) {
            if ($r->{$field}) {
                Storage::disk('local')->delete($r->{$field});
            }
        }

        $r->delete();

        return redirect()->route('admin.ppdb.index')->with('status', 'Data pendaftar dihapus.');
    }

    /** Dokumen ada di disk privat; hanya admin sekolah yang bersangkutan yang bisa membukanya. */
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

    /** CSV (UTF-8) yang bisa dibuka langsung di Excel. */
    public function export(Request $request)
    {
        $rows = $this->query($request)->orderBy('year')->orderBy('seq')->get();
        $statuses = config('school.ppdb_statuses');
        $school = AdminContext::school();

        $headers = ['No. Pendaftaran', 'Tanggal Daftar', 'Status', 'Nama Calon Siswa', 'NIK', 'L/P', 'Tempat Lahir',
            'Tanggal Lahir', 'Agama', 'Alamat', 'Asal Sekolah', 'Nama Orang Tua/Wali', 'No. HP', 'Email', 'Pekerjaan', 'Catatan'];

        return response()->streamDownload(function () use ($rows, $headers, $statuses) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM supaya Excel membaca UTF-8
            fputcsv($out, $headers);

            foreach ($rows as $r) {
                fputcsv($out, array_map([$this, 'safeCell'], [
                    $r->reg_number, $r->created_at->format('d/m/Y H:i'), $statuses[$r->status] ?? $r->status,
                    $r->student_name, $r->nik, $r->gender, $r->birth_place, $r->birth_date->format('d/m/Y'),
                    $r->religion, $r->address, $r->previous_school, $r->parent_name, $r->parent_phone,
                    $r->parent_email, $r->parent_job, $r->admin_note,
                ]));
            }

            fclose($out);
        }, 'pendaftar-'.$school->slug.'-'.now()->format('Ymd').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    // ------------------------------------------------------------------

    private function query(Request $request)
    {
        $status = $request->query('status');
        $q = trim((string) $request->query('q'));

        return Registration::where('school_id', AdminContext::schoolId())
            ->when($status && array_key_exists($status, config('school.ppdb_statuses')), fn ($b) => $b->where('status', $status))
            ->when($q !== '', function ($b) use ($q) {
                $like = '%'.str_replace(['%', '_'], ['\%', '\_'], mb_strtolower($q)).'%';
                $b->where(function ($w) use ($like) {
                    $w->whereRaw('LOWER(student_name) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(reg_number) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(parent_name) LIKE ?', [$like]);
                });
            });
    }

    private function find(int $id): Registration
    {
        return Registration::where('school_id', AdminContext::schoolId())->findOrFail($id);
    }

    /** Mencegah "CSV injection": sel yang diawali = + - @ dibaca Excel sebagai rumus. */
    public function safeCell(mixed $value): string
    {
        $value = (string) $value;

        return $value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true) ? "'".$value : $value;
    }
}