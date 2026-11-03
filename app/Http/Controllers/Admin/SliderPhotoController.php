<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\SliderPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SliderPhotoController extends Controller
{
    public function index(Request $request, School $school)
    {
        $this->authorizeSchool($request, $school);

        return view('admin.slider.index', [
            'school' => $school,
            'photos' => $school->sliderPhotos()->get(),
            'max'    => $this->max(),
        ]);
    }

    public function store(Request $request, School $school)
    {
        $this->authorizeSchool($request, $school);

        if ($school->sliderPhotos()->count() >= $this->max()) {
            return back()->withErrors(['image' => 'Maksimal ' . $this->max() . ' foto. Hapus salah satu dulu.']);
        }

        $data = $request->validate([
            'image'   => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:' . config('school.slider.max_size_kb')],
            'caption' => ['nullable', 'string', 'max:120'],
        ], $this->messages());

        $school->sliderPhotos()->create([
            'image'      => $request->file('image')->store('slider', 'public'),
            'caption'    => $data['caption'] ?? null,
            'sort_order' => (int) $school->sliderPhotos()->max('sort_order') + 1,
        ]);

        return back()->with('ok', 'Foto slider berhasil ditambahkan.');
    }

    public function update(Request $request, School $school, SliderPhoto $photo)
    {
        $this->authorizeSchool($request, $school);
        abort_unless($photo->school_id === $school->id, 404);

        $data = $request->validate([
            'caption'    => ['nullable', 'string', 'max:120'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:99'],
            'image'      => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:' . config('school.slider.max_size_kb')],
        ], $this->messages());

        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($photo->image);
            $photo->image = $request->file('image')->store('slider', 'public');
        }

        $photo->fill([
            'caption'    => $data['caption'] ?? null,
            'sort_order' => $data['sort_order'],
            'is_active'  => $request->boolean('is_active'),
        ])->save();

        return back()->with('ok', 'Foto slider diperbarui.');
    }

    public function destroy(Request $request, School $school, SliderPhoto $photo)
    {
        $this->authorizeSchool($request, $school);
        abort_unless($photo->school_id === $school->id, 404);

        Storage::disk('public')->delete($photo->image);
        $photo->delete();

        return back()->with('ok', 'Foto slider dihapus.');
    }

    private function max(): int
    {
        return (int) config('school.slider.max_photos', 3);
    }

    /**
     * super_admin boleh semua sekolah, admin hanya sekolahnya sendiri,
     * operator tidak boleh mengubah slider.
     * Sesuaikan "role" dan "school_id" dengan nama kolom di tabel users Anda.
     */
    private function authorizeSchool(Request $request, School $school): void
    {
        $user = $request->user();

        abort_if($user === null, 403);

        if ($user->role === 'super_admin') {
            return;
        }

        abort_unless(
            $user->role === 'admin' && (int) $user->school_id === (int) $school->id,
            403
        );
    }

    private function messages(): array
    {
        return [
            'image.required' => 'Pilih foto terlebih dahulu.',
            'image.image'    => 'File harus berupa gambar.',
            'image.mimes'    => 'Format yang diizinkan: JPG, PNG, WEBP.',
            'image.max'      => 'Ukuran foto maksimal 3 MB.',
        ];
    }
}