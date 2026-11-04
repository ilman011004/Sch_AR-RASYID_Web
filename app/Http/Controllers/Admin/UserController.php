<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Super admin : mengelola semua akun panel admin.
 * Admin sekolah: hanya membuat/mengelola OPERATOR di sekolahnya sendiri.
 * Akun orang tua/wali (role "parent") dibuat sendiri lewat halaman PPDB dan tidak dikelola di sini.
 */
class UserController extends Controller
{
    public function index(Request $request)
    {
        $me = $request->user();

        $users = User::with('school')
            ->where('role', '!=', 'parent')
            ->when(! $me->isSuperAdmin(), fn ($q) => $q->where('school_id', $me->school_id)->where('role', 'operator'))
            ->orderBy('role')->orderBy('name')
            ->get();

        return view('admin.users.index', ['users' => $users]);
    }

    public function create()
    {
        return view('admin.users.form', $this->formData(null));
    }

    public function store(Request $request)
    {
        $me = $request->user();
        $data = $this->validated($request, null);

        $role = $me->isSuperAdmin() ? $data['role'] : 'operator';

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $role,
            'school_id' => $this->schoolFor($me, $role, $data['school_id'] ?? null),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.users.index')->with('status', 'Akun dibuat.');
    }

    public function edit(Request $request, User $user)
    {
        $this->authorizeTarget($request, $user);

        return view('admin.users.form', $this->formData($user));
    }

    public function update(Request $request, User $user)
    {
        $this->authorizeTarget($request, $user);

        $me = $request->user();
        $data = $this->validated($request, $user);

        $update = [
            'name' => $data['name'],
            'email' => $data['email'],
            // Tidak boleh menonaktifkan akun sendiri.
            'is_active' => $user->is($me) ? true : $request->boolean('is_active'),
        ];

        if ($me->isSuperAdmin() && ! $user->is($me)) {
            $update['role'] = $data['role'];
            $update['school_id'] = $this->schoolFor($me, $data['role'], $data['school_id'] ?? null);
        }

        if (! empty($data['password'])) {
            $update['password'] = $data['password'];
        }

        $user->update($update);

        return redirect()->route('admin.users.index')->with('status', 'Akun diperbarui.');
    }

    public function destroy(Request $request, User $user)
    {
        $this->authorizeTarget($request, $user);
        abort_if($user->is($request->user()), 403, 'Tidak bisa menghapus akun sendiri.');

        $user->delete();

        return redirect()->route('admin.users.index')->with('status', 'Akun dihapus.');
    }

    // ------------------------------------------------------------------

    private function authorizeTarget(Request $request, User $target): void
    {
        // Akun orang tua tidak dikelola dari panel ini (siapa pun perannya).
        abort_if($target->isParent(), 404);

        $me = $request->user();

        if ($me->isSuperAdmin()) {
            return;
        }

        abort_unless($target->school_id === $me->school_id && $target->role === 'operator', 403);
    }

    private function schoolFor(User $me, string $role, int|string|null $requested): ?int
    {
        if (! $me->isSuperAdmin()) {
            return $me->school_id;
        }

        return $role === 'super_admin' ? null : (int) $requested;
    }

    private function formData(?User $user): array
    {
        return [
            'target' => $user,
            'schools' => School::orderBy('sort')->orderBy('id')->get(),
            'roles' => config('school.roles'), // sengaja tanpa "parent"
        ];
    }

    private function validated(Request $request, ?User $user): array
    {
        $me = $request->user();
        $creating = $user === null;

        $rules = [
            'name' => 'required|string|max:150',
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user?->id)],
            'password' => [$creating ? 'required' : 'nullable', 'confirmed', Password::min(10)],
            'is_active' => 'boolean',
        ];

        if ($me->isSuperAdmin()) {
            $rules['role'] = ['required', Rule::in(array_keys(config('school.roles')))];
            $rules['school_id'] = [
                Rule::requiredIf(fn () => $request->input('role') !== 'super_admin'),
                'nullable', 'integer', 'exists:schools,id',
            ];
        }

        return $request->validate($rules, [], [
            'name' => 'nama', 'password' => 'kata sandi', 'role' => 'peran', 'school_id' => 'sekolah',
        ]);
    }
}