<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function edit(Request $request)
    {
        $school = AdminContext::school();

        // Hanya field yang boleh dilihat peran ini & jenis sekolah ini; grup kosong dibuang.
        $groups = collect(config('admin.school_fields'))
            ->map(fn ($fields) => collect($fields)
                ->filter(fn ($f) => $this->allowed($f, $school, $request->user()))
                ->values()->all())
            ->filter()
            ->all();

        return view('admin.settings', [
            'school' => $school,
            'groups' => $groups,
        ]);
    }

    public function update(Request $request)
    {
        $school = AdminContext::school();

        // Field yang tidak diizinkan tidak divalidasi, tidak disimpan, dan tidak ditimpa.
        $fields = collect(config('admin.school_fields'))->flatten(1)
            ->filter(fn ($f) => $this->allowed($f, $school, $request->user()))
            ->values();

        $rules = [];
        foreach ($fields as $f) {
            $rules[$f['name']] = $f['type'] === 'image'
                ? 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048'
                : ($f['rules'] ?? 'nullable');
        }

        $valid = $request->validate($rules, [], $fields->pluck('label', 'name')->all());

        $data = [];
        foreach ($fields as $f) {
            $name = $f['name'];

            if ($f['type'] === 'image') {
                if ($request->hasFile($name)) {
                    $data[$name] = $request->file($name)->store('school', 'public');

                    if ($school->{$name}) {
                        Storage::disk('public')->delete($school->{$name});
                    }
                }
            } elseif ($f['type'] === 'checkbox') {
                $data[$name] = $request->boolean($name);
            } else {
                $data[$name] = $valid[$name] ?? null;
            }
        }

        $school->update($data);

        return back()->with('status', 'Pengaturan sekolah disimpan.');
    }

    /**
     * Apakah field ini boleh dilihat/diubah oleh pengguna untuk sekolah yang sedang dikelola?
     * Mendukung: 'only' => 'foundation', 'except' => 'foundation', 'roles' => [...].
     */
    private function allowed(array $field, $school, $user): bool
    {
        if (($field['only'] ?? null) === 'foundation' && ! $school->isFoundation()) {
            return false;
        }

        if (($field['except'] ?? null) === 'foundation' && $school->isFoundation()) {
            return false;
        }

        if (isset($field['roles']) && ! in_array($user->role, $field['roles'], true)) {
            return false;
        }

        return true;
    }
}