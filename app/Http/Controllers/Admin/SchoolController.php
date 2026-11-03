<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Hanya super admin (dijaga middleware di rute). Menambah/mengatur jenjang. */
class SchoolController extends Controller
{
    public function index()
    {
        return view('admin.schools.index', [
            'schools' => School::jenjang()->orderBy('sort')->orderBy('id')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.schools.form', ['school' => null]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, null);

        School::create($data + [
            'head_title' => config('school.head_titles.'.$data['level'], 'Kepala Sekolah'),
            'foundation_name' => config('school.foundation_name'),
        ]);

        return redirect()->route('admin.schools.index')->with('status', 'Sekolah ditambahkan.');
    }

    public function edit(School $managed)
    {
        abort_if($managed->isFoundation(), 404);

        return view('admin.schools.form', ['school' => $managed]);
    }

    public function update(Request $request, School $managed)
    {
        abort_if($managed->isFoundation(), 404);

        $managed->update($this->validated($request, $managed));

        return redirect()->route('admin.schools.index')->with('status', 'Sekolah diperbarui.');
    }

    private function validated(Request $request, ?School $school): array
    {
        $valid = $request->validate([
            'name' => 'required|string|max:150',
            'slug' => [
                'required', 'alpha_dash:ascii', 'lowercase', 'max:30',
                Rule::notIn(config('school.reserved_slugs')),
                Rule::unique('schools', 'slug')->ignore($school?->id),
            ],
            'level' => ['required', Rule::in(array_keys(config('school.levels')))],
            'sort' => 'nullable|integer|min:0|max:999',
            'is_active' => 'boolean',
        ], [], ['name' => 'nama', 'slug' => 'alamat singkat (slug)', 'level' => 'jenjang']);

        return [
            'name' => $valid['name'],
            'slug' => $valid['slug'],
            'level' => $valid['level'],
            'sort' => (int) ($valid['sort'] ?? 0),
            'is_active' => $request->boolean('is_active'),
        ];
    }
}