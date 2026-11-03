<?php

namespace Database\Seeders;

use App\Models\School;
use Illuminate\Database\Seeder;

class FoundationSeeder extends Seeder
{
    public function run(): void
    {
        $name = config('school.foundation_name');

        // is_active = false: baris ini tidak punya situs /yayasan sendiri,
        // tampilannya adalah halaman utama situs.
        $f = School::firstOrCreate(['slug' => config('school.foundation_slug')], [
            'name' => $name,
            'level' => 'yayasan',
            'is_active' => false,
            'sort' => 0,
            'primary_color' => '#0f766e',
            'head_title' => 'Ketua Yayasan',
            'foundation_name' => $name,
            'tagline' => 'Selamat datang di '.$name,
        ]);

        if (! $f->wasRecentlyCreated) {
            return;
        }

        $f->update([
            'description' => '[CONTOH - ganti] Deskripsi singkat yayasan.',
            'head_name' => '[Nama Ketua Yayasan]',
            'welcome_message' => '[CONTOH - ganti] Kata sambutan ketua yayasan.',
            'vision' => '[CONTOH - ganti] Visi yayasan.',
            'mission' => "[CONTOH] Misi pertama\n[CONTOH] Misi kedua",
            'goals' => "[CONTOH] Tujuan pertama\n[CONTOH] Tujuan kedua",
            'history' => '[CONTOH - ganti] Sejarah berdirinya yayasan.',
            'ppdb_info' => "## Syarat Umum\n[CONTOH] Fotokopi akta kelahiran\n[CONTOH] Fotokopi kartu keluarga\n## Alur Pendaftaran\n[CONTOH] Pilih jenjang\n[CONTOH] Isi formulir online di situs jenjang",
        ]);

        $f->staff()->create(['name' => '[Nama Sekretaris]', 'position' => 'Sekretaris Yayasan', 'staff_group' => 'kependidikan', 'sort' => 0]);
        $f->staff()->create(['name' => '[Nama Bendahara]', 'position' => 'Bendahara Yayasan', 'staff_group' => 'kependidikan', 'sort' => 1]);
    }
}