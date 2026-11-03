<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('school.seed_admin_email');
        $password = config('school.seed_admin_password');

        if (! $email || ! $password || strlen($password) < 10) {
            throw new \RuntimeException(
                'Isi ADMIN_EMAIL dan ADMIN_PASSWORD (minimal 10 karakter) di file .env sebelum menjalankan seeder.'
            );
        }

        $foundation = config('school.foundation_name');

        // Satu baris per jenjang. Hanya SD yang aktif dulu; yang lain diaktifkan dari panel admin
        // (menu Daftar Sekolah) setelah kontennya siap.
        $levels = [
            ['slug' => 'paud', 'name' => 'PAUD AR-Rasyid', 'level' => 'paud', 'color' => '#db2777', 'active' => false],
            ['slug' => 'sd',   'name' => 'SD AR-Rasyid',   'level' => 'sd',   'color' => '#0f766e', 'active' => true],
            ['slug' => 'smp',  'name' => 'SMP AR-Rasyid',  'level' => 'smp',  'color' => '#1d4ed8', 'active' => false],
            ['slug' => 'sma',  'name' => 'SMA AR-Rasyid',  'level' => 'sma',  'color' => '#7c3aed', 'active' => false],
        ];

        foreach ($levels as $i => $l) {
            School::firstOrCreate(['slug' => $l['slug']], [
                'name' => $l['name'],
                'level' => $l['level'],
                'is_active' => $l['active'],
                'sort' => $i + 1,
                'primary_color' => $l['color'],
                'head_title' => config('school.head_titles.'.$l['level']),
                'foundation_name' => $foundation,
                'tagline' => 'Selamat datang di '.$l['name'],
            ]);
        }

        // Super admin (yayasan)
        User::updateOrCreate(['email' => $email], [
            'name' => config('school.seed_admin_name'),
            'password' => $password,
            'role' => 'super_admin',
            'school_id' => null,
            'is_active' => true,
        ]);

        $sd = School::where('slug', 'sd')->first();

        if ($sd) {
            $this->sampleSd($sd);
        }

        $this->call(FoundationSeeder::class);
    }

    /** Contoh isi supaya halaman tidak kosong. SEMUA teks ini harus diganti lewat panel admin. */
    private function sampleSd(School $sd): void
    {
        if ($sd->advantages()->exists()) {
            return; // sudah pernah diisi
        }

        $sd->update([
            'tagline' => 'Mendidik dengan hati, membentuk generasi berakhlak dan berprestasi',
            'description' => '[CONTOH - ganti] Deskripsi singkat SD AR-Rasyid.',
            'head_name' => '[Nama Kepala Sekolah]',
            'welcome_message' => '[CONTOH - ganti] Kata sambutan kepala sekolah untuk orang tua dan calon siswa.',
            'vision' => '[CONTOH - ganti] Visi sekolah.',
            'mission' => "[CONTOH] Misi pertama\n[CONTOH] Misi kedua\n[CONTOH] Misi ketiga",
            'goals' => "[CONTOH] Tujuan pendidikan pertama\n[CONTOH] Tujuan pendidikan kedua",
            'history' => '[CONTOH - ganti] Sejarah singkat berdirinya sekolah.',
            'curriculum' => '[CONTOH - ganti] Kurikulum yang digunakan.',
            'ppdb_year' => (int) now()->year,
            'ppdb_info' => "## Syarat Pendaftaran\n[CONTOH] Fotokopi akta kelahiran\n[CONTOH] Fotokopi kartu keluarga\n[CONTOH] Pas foto\n## Alur Pendaftaran\n[CONTOH] Isi formulir online\n[CONTOH] Verifikasi berkas\n[CONTOH] Pengumuman hasil",
        ]);

        foreach ([
            ['📖', 'Pembelajaran berkualitas', '[CONTOH] Jelaskan keunggulan pertama.'],
            ['🕌', 'Pembinaan akhlak', '[CONTOH] Jelaskan keunggulan kedua.'],
            ['🏆', 'Prestasi siswa', '[CONTOH] Jelaskan keunggulan ketiga.'],
            ['🌱', 'Lingkungan yang aman', '[CONTOH] Jelaskan keunggulan keempat.'],
        ] as $i => [$icon, $title, $desc]) {
            $sd->advantages()->create(['icon' => $icon, 'title' => $title, 'description' => $desc, 'sort' => $i]);
        }

        foreach ([
            ['Bagaimana cara mendaftar?', '[CONTOH] Buka halaman Pendaftaran Siswa, baca syarat dan alurnya, lalu isi formulir online.'],
            ['Bagaimana cara menghubungi sekolah?', '[CONTOH] Hubungi kami lewat halaman Kontak, telepon, atau WhatsApp yang tertera di bagian bawah halaman.'],
        ] as $i => [$q, $a]) {
            $sd->faqs()->create(['question' => $q, 'answer' => $a, 'sort' => $i]);
        }

        $sd->programs()->create(['kind' => 'unggulan', 'title' => '[CONTOH] Program unggulan', 'description' => 'Deskripsi program.', 'sort' => 0]);
        $sd->programs()->create(['kind' => 'ekstrakurikuler', 'title' => '[CONTOH] Pramuka', 'description' => 'Deskripsi kegiatan.', 'sort' => 0]);

        $sd->staff()->create(['name' => '[Nama Guru]', 'position' => 'Guru Kelas', 'staff_group' => 'pendidik', 'sort' => 0]);
        $sd->staff()->create(['name' => '[Nama Staf]', 'position' => 'Tata Usaha', 'staff_group' => 'kependidikan', 'sort' => 0]);

        $sd->posts()->create([
            'type' => 'berita',
            'title' => 'Contoh berita kegiatan sekolah',
            'slug' => 'contoh-berita-kegiatan-sekolah',
            'excerpt' => '[CONTOH] Ringkasan berita.',
            'body' => "[CONTOH] Isi berita pertama.\n\nHapus atau ubah berita ini lewat panel admin.",
            'status' => 'published',
            'published_at' => now(),
        ]);
    }
}