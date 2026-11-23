<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FormBuilderController extends Controller
{
    public function edit()
    {
        $school = AdminContext::school();

        if ($school->isFoundation()) {
            return redirect()->route('admin.dashboard')
                ->with('status', 'Pengaturan formulir pendaftaran hanya berlaku untuk jenjang pendidikan (PAUD, SD, SMP, SMA).');
        }

        $schema = $school->getFormSchema();

        return view('admin.ppdb.form-builder', [
            'school' => $school,
            'schema' => $schema,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $school = AdminContext::school();

        if ($school->isFoundation()) {
            abort(403, 'Yayasan tidak memiliki formulir pendaftaran langsung.');
        }

        $rawPages = $request->input('pages', []);

        if (! is_array($rawPages) || empty($rawPages)) {
            return back()->withErrors(['pages' => 'Formulir harus memiliki minimal 1 halaman/bagian.']);
        }

        $cleanPages = [];
        $pageIndex = 1;

        foreach ($rawPages as $p) {
            $pageTitle = trim($p['title'] ?? '') ?: ('Halaman '.$pageIndex);
            $pageSubtitle = trim($p['subtitle'] ?? '');

            $fields = [];
            if (! empty($p['fields']) && is_array($p['fields'])) {
                foreach ($p['fields'] as $f) {
                    $label = trim($f['label'] ?? '');
                    if ($label === '') {
                        continue;
                    }

                    $type = in_array($f['type'] ?? '', ['text', 'paragraph', 'number', 'date', 'select', 'radio', 'checkbox', 'file'], true)
                        ? $f['type']
                        : 'text';

                    $id = ! empty($f['id']) ? preg_replace('/[^a-zA-Z0-9_]/', '', $f['id']) : '';
                    if (! $id) {
                        $id = 'f_'.substr(md5($label.microtime().rand()), 0, 8);
                    }

                    $mapping = in_array($f['mapping'] ?? '', [
                        'student_name', 'nik', 'gender', 'birth_place', 'birth_date', 'religion',
                        'address', 'previous_school', 'parent_name', 'parent_phone', 'parent_email',
                        'parent_job', 'doc_birth_cert', 'doc_family_card', 'doc_photo',
                    ], true) ? $f['mapping'] : null;

                    $options = [];
                    if (in_array($type, ['select', 'radio', 'checkbox'], true)) {
                        $rawOptions = $f['options'] ?? '';
                        if (is_string($rawOptions)) {
                            $options = array_values(array_filter(array_map('trim', preg_split('/\R/', $rawOptions))));
                        } elseif (is_array($rawOptions)) {
                            $options = array_values(array_filter(array_map('trim', $rawOptions)));
                        }
                    }

                    $fields[] = [
                        'id' => $id,
                        'mapping' => $mapping,
                        'label' => $label,
                        'type' => $type,
                        'required' => ! empty($f['required']),
                        'help' => trim($f['help'] ?? ''),
                        'options' => $options,
                    ];
                }
            }

            $cleanPages[] = [
                'id' => 'page_'.$pageIndex,
                'title' => $pageTitle,
                'subtitle' => $pageSubtitle,
                'fields' => $fields,
            ];

            $pageIndex++;
        }

        $school->update([
            'form_schema' => [
                'pages' => $cleanPages,
            ],
        ]);

        return back()->with('status', 'Susunan formulir pendaftaran berhasil disimpan.');
    }

    public function reset(): RedirectResponse
    {
        $school = AdminContext::school();
        $school->update(['form_schema' => null]);

        return back()->with('status', 'Formulir berhasil direset ke susunan awal.');
    }
}
