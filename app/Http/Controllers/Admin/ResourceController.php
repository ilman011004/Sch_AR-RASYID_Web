<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Satu pengendali untuk semua modul CRUD sederhana.
 * Definisi modul ada di config/admin.php. Semua data otomatis dibatasi ke sekolah aktif.
 * Tipe berkas: image (foto) dan video.
 */
class ResourceController extends Controller
{
    private const FILE_TYPES = ['image', 'video'];

    public function index(Request $request, string $resource)
    {
        $d = $this->definition($request, $resource);
        $userId = $request->user()->id;

        $items = $d['model']::where('school_id', AdminContext::schoolId())
            ->when($this->ownOnly($request, $d), fn ($q) => $q->where('user_id', $userId))
            ->orderBy($d['order'][0], $d['order'][1])
            ->orderBy('id', 'desc')
            ->paginate(20);

        return view('admin.resource.index', compact('d', 'resource', 'items'));
    }

    public function create(Request $request, string $resource)
    {
        $d = $this->definition($request, $resource);

        return view('admin.resource.form', ['d' => $d, 'resource' => $resource, 'item' => null]);
    }

    public function store(Request $request, string $resource)
    {
        $d = $this->definition($request, $resource);
        $this->persist($request, $d, null);

        return redirect()->route('admin.resource.index', $resource)->with('status', 'Data berhasil ditambahkan.');
    }

    public function edit(Request $request, string $resource, int $id)
    {
        $d = $this->definition($request, $resource);
        $item = $this->find($request, $d, $id);

        return view('admin.resource.form', compact('d', 'resource', 'item'));
    }

    public function update(Request $request, string $resource, int $id)
    {
        $d = $this->definition($request, $resource);
        $this->persist($request, $d, $this->find($request, $d, $id));

        return redirect()->route('admin.resource.index', $resource)->with('status', 'Perubahan disimpan.');
    }

    public function destroy(Request $request, string $resource, int $id)
    {
        $d = $this->definition($request, $resource);
        $item = $this->find($request, $d, $id);

        foreach ($d['fields'] as $f) {
            if (in_array($f['type'], self::FILE_TYPES, true) && $item->{$f['name']}) {
                Storage::disk('public')->delete($item->{$f['name']});
            }
        }

        $item->delete();

        return redirect()->route('admin.resource.index', $resource)->with('status', 'Data dihapus.');
    }

    // ------------------------------------------------------------------

    private function definition(Request $request, string $resource): array
    {
        $d = config("admin.resources.$resource");

        abort_unless($d, 404);
        abort_unless(in_array($request->user()->role, $d['roles'], true), 403);

        // Modul khusus yayasan: tidak ada untuk sekolah jenjang.
        if (($d['only'] ?? null) === 'foundation') {
            abort_unless(AdminContext::school()->isFoundation(), 404);
        }

        return $d;
    }

    private function ownOnly(Request $request, array $d): bool
    {
        return $request->user()->role === 'operator' && ! empty($d['own_only']);
    }

    private function find(Request $request, array $d, int $id): Model
    {
        $item = $d['model']::where('school_id', AdminContext::schoolId())->findOrFail($id);

        if ($this->ownOnly($request, $d) && $item->user_id !== $request->user()->id) {
            abort(403);
        }

        return $item;
    }

    private function rules(array $d, bool $creating): array
    {
        $rules = [];

        foreach ($d['fields'] as $f) {
            if ($f['type'] === 'image') {
                $required = $creating && ! empty($f['required_on_create']) ? 'required' : 'nullable';
                $rules[$f['name']] = $required.'|image|mimes:jpg,jpeg,png,webp|max:2048';
            } elseif ($f['type'] === 'video') {
                $rules[$f['name']] = 'nullable|file|mimetypes:video/mp4,video/webm|max:'.($f['max_kb'] ?? 51200);
            } else {
                $rules[$f['name']] = $f['rules'] ?? 'nullable';
            }
        }

        return $rules;
    }

    private function persist(Request $request, array $d, ?Model $item): Model
    {
        $labels = collect($d['fields'])->pluck('label', 'name')->all();
        $valid = $request->validate($this->rules($d, $item === null), [], $labels);

        $data = [];

        foreach ($d['fields'] as $f) {
            $name = $f['name'];

            if (in_array($f['type'], self::FILE_TYPES, true)) {
                if ($request->hasFile($name)) {
                    $data[$name] = $request->file($name)->store($d['dir'], 'public');

                    if ($item && $item->{$name}) {
                        Storage::disk('public')->delete($item->{$name});
                    }
                }
            } elseif ($f['type'] === 'checkbox') {
                $data[$name] = $request->boolean($name);
            } else {
                $data[$name] = $valid[$name] ?? ($f['default'] ?? null);
            }
        }

        $data['school_id'] = AdminContext::schoolId();

        if (method_exists($d['model'], 'adminPrepare')) {
            $data = $d['model']::adminPrepare($data, $request->user(), $item);
        }

        if ($item) {
            $item->update($data);

            return $item;
        }

        return $d['model']::create($data);
    }
}