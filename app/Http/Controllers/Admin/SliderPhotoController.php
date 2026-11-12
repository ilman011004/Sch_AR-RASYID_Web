<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\SliderPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

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
            return back()->withErrors(['image' => 'Maksimal ' . $this->max() . ' item. Hapus salah satu dulu.']);
        }

        $data = $request->validate([
            'image'     => ['nullable', 'required_without:video_url', 'image', 'mimes:jpg,jpeg,png,webp', 'max:' . config('school.slider.max_size_kb')],
            'video_url' => ['nullable', 'required_without:image', 'url', 'max:255', 'regex:~(youtu\.be/|youtube\.com/)~'],
            'caption'   => ['nullable', 'string', 'max:120'],
        ], $this->messages());

        $photo = new SliderPhoto([
            'caption'    => $data['caption'] ?? null,
            'video_url'  => $data['video_url'] ?? null,
            'sort_order' => (int) $school->sliderPhotos()->max('sort_order') + 1,
        ]);

        if ($request->hasFile('image')) {
            $photo->image = $request->file('image')->store('slider', 'public');
        }

        $school->sliderPhotos()->save($photo);

        return back()->with('ok', 'Slider berhasil ditambahkan.');
    }

    public function update(Request $request, School $school, SliderPhoto $photo)
    {
        $this->authorizeSchool($request, $school);
        abort_unless($photo->school_id === $school->id, 404);

        $data = $request->validate([
            'caption'    => ['nullable', 'string', 'max:120'],
            'video_url'  => ['nullable', 'url', 'max:255', 'regex:~(youtu\.be/|youtube\.com/)~'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:99'],
            'image'      => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:' . config('school.slider.max_size_kb')],
        ], $this->messages());

        // Harus tetap punya minimal satu: foto atau link YouTube.
        if (! $request->hasFile('image') && ! $photo->image && empty($data['video_url'])) {
            throw ValidationException::withMessages([
                'video_url' => 'Isi foto atau link YouTube.',
            ]);
        }

        if ($request->hasFile('image')) {
            if ($photo->image) {
                Storage::disk('public')->delete($photo->image);
            }
            $photo->image = $request->file('image')->store('slider', 'public');
        }

        $photo->fill([
            'caption'    => $data['caption'] ?? null,
            'video_url'  => $data['video_url'] ?? null,
            'sort_order' => $data['sort_order'],
            'is_active'  => $request->boolean('is_active'),
        ])->save();

        return back()->with('ok', 'Slider diperbarui.');
    }

    public function destroy(Request $request, School $school, SliderPhoto $photo)
    {
        $this->authorizeSchool($request, $school);
        abort_unless($photo->school_id === $school->id, 404);

        if ($photo->image) {
            Storage::disk('public')->delete($photo->image);
        }
        $photo->delete();

        return back()->with('ok', 'Slider dihapus.');
    }

    private function max(): int
    {
        return (int) config('school.slider.max_photos', 3);
    }

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
            'image.required_without'     => 'Pilih foto atau isi link YouTube.',
            'video_url.required_without' => 'Pilih foto atau isi link YouTube.',
            'image.image'                => 'File harus berupa gambar.',
            'image.mimes'                => 'Format yang diizinkan: JPG, PNG, WEBP.',
            'image.max'                  => 'Ukuran foto maksimal 3 MB.',
            'video_url.url'              => 'Link tidak valid.',
            'video_url.regex'            => 'Link harus dari YouTube (youtube.com atau youtu.be).',
        ];
    }
}