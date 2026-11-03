<?php

namespace App\Support;

use App\Models\School;

/**
 * Menentukan sekolah yang sedang dikelola di panel admin.
 *  - admin / operator : selalu sekolahnya sendiri (tidak bisa diubah dari browser)
 *  - super admin      : sekolah yang dipilih lewat pemilih sekolah (disimpan di sesi)
 */
class AdminContext
{
    public static function schoolId(): int
    {
        $user = request()->user();

        if (! $user->isSuperAdmin()) {
            abort_unless($user->school_id, 403, 'Akun ini belum terikat ke sekolah.');

            return (int) $user->school_id;
        }

        $id = session('admin_school_id');

        if (! $id || ! School::whereKey($id)->exists()) {
            $id = School::orderBy('sort')->orderBy('id')->value('id');
            abort_unless($id, 404, 'Belum ada sekolah. Buat sekolah dulu di menu Sekolah.');
            session(['admin_school_id' => $id]);
        }

        return (int) $id;
    }

    public static function school(): School
    {
        return School::findOrFail(self::schoolId());
    }
}