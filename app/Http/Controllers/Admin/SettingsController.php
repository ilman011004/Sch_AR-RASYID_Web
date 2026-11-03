<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function edit()
    {
        return view('admin.settings', [
            'school' => AdminContext::school(),
            'groups' => config('admin.school_fields'),
        ]);
    }

    public function update(Request $request)
    {
        $school = AdminContext::school();
        $fields = collect(config('admin.school_fields'))->flatten(1);

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
}