<?php

namespace App\Models;

use App\Models\Advantage;
use App\Models\ContactMessage;
use App\Models\Faq;
use App\Models\Gallery;
use App\Models\Post;
use App\Models\Program;
use App\Models\Registration;
use App\Models\Slide;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class School extends Model
{
    protected $guarded = ['id'];

    private static ?self $foundation = null;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'ppdb_open' => 'boolean',
            'ppdb_start' => 'date',
            'ppdb_end' => 'date',
            'form_schema' => 'array',
        ];
    }

    /** URL memakai slug: /sd atau sd.domain */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function galleries(): HasMany
    {
        return $this->hasMany(Gallery::class);
    }

    public function slides(): HasMany
    {
        return $this->hasMany(Slide::class);
    }

    public function staff(): HasMany
    {
        return $this->hasMany(Staff::class);
    }

    public function programs(): HasMany
    {
        return $this->hasMany(Program::class);
    }

    public function advantages(): HasMany
    {
        return $this->hasMany(Advantage::class);
    }

    public function faqs(): HasMany
    {
        return $this->hasMany(Faq::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ContactMessage::class);
    }

    public function levelLabel(): string
    {
        return config('school.levels.'.$this->level, strtoupper($this->level));
    }

    /** Baris khusus yayasan (level = yayasan). */
    public static function foundation(): self
    {
        return self::$foundation ??= static::where('level', 'yayasan')->first()
            ?? abort(503, 'Data yayasan belum ada. Jalankan: php artisan db:seed --class=FoundationSeeder');
    }

    /** Hanya jenjang pendidikan (tanpa baris yayasan). */
    public function scopeJenjang($q)
    {
        return $q->where('level', '!=', 'yayasan');
    }

    public function isFoundation(): bool
    {
        return $this->level === 'yayasan';
    }

    /**
     * Mengambil skema formulir pendaftaran PPDB murni Google Form-style.
     * Jika belum diatur khusus, dibuatkan default pertanyaan yang bisa diedit/ditambah/dihapus bebas oleh admin.
     */
    public function getFormSchema(): array
    {
        $schema = $this->form_schema;
        if (! empty($schema) && is_array($schema) && ! empty($schema['pages'])) {
            return $schema;
        }

        return [
            'pages' => [
                [
                    'id' => 'page_1',
                    'title' => 'Data Calon Siswa',
                    'subtitle' => 'Isi data diri calon siswa sesuai akta kelahiran',
                    'fields' => [
                        self::formField('f_student_name', 'student_name', 'Nama Lengkap Calon Siswa', 'text', true, 'Nama sesuai akta kelahiran'),
                        self::formField('f_gender', 'gender', 'Jenis Kelamin', 'radio', true, '', ['Laki-laki', 'Perempuan']),
                        self::formField('f_birth_place', 'birth_place', 'Tempat Lahir', 'text', true),
                        self::formField('f_birth_date', 'birth_date', 'Tanggal Lahir', 'date', true),
                        self::formField('f_address', 'address', 'Alamat Tempat Tinggal', 'region', true, 'Pilih Provinsi sampai Desa/Kelurahan, lalu isi nama jalan, RT/RW'),
                    ],
                ],
                [
                    'id' => 'page_2',
                    'title' => 'Identitas Orang Tua / Wali',
                    'subtitle' => 'Isi data ayah dan ibu. Data wali hanya diisi bila calon siswa tinggal bersama wali',
                    'fields' => self::parentFormFields(),
                ],
                [
                    'id' => 'page_3',
                    'title' => 'Riwayat Pendidikan & Prestasi',
                    'subtitle' => 'Isi sesuai riwayat calon siswa. Kosongkan bagian yang tidak ada',
                    'fields' => self::educationFormFields(),
                ],
                [
                    'id' => 'page_4',
                    'title' => 'Riwayat Kesehatan',
                    'subtitle' => 'Kondisi kesehatan calon siswa untuk membantu sekolah memberi layanan terbaik',
                    'fields' => self::healthFormFields(),
                ],
                [
                    'id' => 'page_5',
                    'title' => 'Lain-Lain',
                    'subtitle' => 'Kebiasaan ibadah dan makanan calon siswa di rumah',
                    'fields' => self::otherFormFields(),
                ],
                [
                    'id' => 'page_6',
                    'title' => 'Unggah Berkas Dokumen',
                    'subtitle' => 'Unggah dokumen dalam format gambar (JPG/PNG) atau PDF (maks. 2 MB)',
                    'fields' => [
                        self::formField('f_doc_birth_cert', 'doc_birth_cert', 'Akta Kelahiran', 'file', true, 'File JPG, PNG, atau PDF'),
                        self::formField('f_doc_family_card', 'doc_family_card', 'Kartu Keluarga', 'file', true, 'File JPG, PNG, atau PDF'),
                        self::formField('f_doc_photo', 'doc_photo', 'Pas Foto Calon Siswa (3x4 atau 4x6)', 'file', true, 'Foto berwarna terbaru calon siswa'),
                    ],
                ],
            ],
        ];
    }

    /** Satu pertanyaan formulir dalam bentuk array yang sesuai dengan skema. */
    private static function formField(string $id, ?string $mapping, string $label, string $type, bool $required = false, string $help = '', array $options = []): array
    {
        return compact('id', 'mapping', 'label', 'type', 'required', 'help', 'options');
    }

    /** Judul bagian (pembatas, tidak menghasilkan jawaban). */
    private static function formSection(string $id, string $title, string $help = ''): array
    {
        return self::formField($id, null, $title, 'section', false, $help);
    }

    /**
     * Template "Data Orang Tua / Wali": Ayah, Ibu, Wali (opsional), dan kontak, semuanya dalam satu halaman.
     * Dipakai oleh formulir default dan oleh tombol template di pengaturan formulir.
     */
    public static function parentFormFields(): array
    {
        $education = ['Tidak sekolah', 'SD / sederajat', 'SMP / sederajat', 'SMA / sederajat', 'D1 - D3', 'S1 / D4', 'S2', 'S3'];
        $income = ['Kurang dari Rp 1.000.000', 'Rp 1.000.000 - Rp 2.000.000', 'Rp 2.000.000 - Rp 5.000.000', 'Rp 5.000.000 - Rp 10.000.000', 'Lebih dari Rp 10.000.000'];

        return [
            self::formSection('f_sec_father', 'Data Ayah', 'Isi sesuai Kartu Keluarga'),
            self::formField('f_father_name', 'parent_name', 'Nama Lengkap', 'text', true),
            self::formField('f_father_nik', null, 'NIK', 'number', false, '16 digit sesuai KTP'),
            self::formField('f_father_job', 'parent_job', 'Pekerjaan', 'text'),
            self::formField('f_father_edu', null, 'Pendidikan Terakhir', 'select', false, '', $education),
            self::formField('f_father_income', null, 'Penghasilan per Bulan', 'select', false, '', $income),

            self::formSection('f_sec_mother', 'Data Ibu', 'Isi sesuai Kartu Keluarga'),
            self::formField('f_mother_name', null, 'Nama Lengkap', 'text', true),
            self::formField('f_mother_nik', null, 'NIK', 'number', false, '16 digit sesuai KTP'),
            self::formField('f_mother_job', null, 'Pekerjaan', 'text'),
            self::formField('f_mother_edu', null, 'Pendidikan Terakhir', 'select', false, '', $education),
            self::formField('f_mother_income', null, 'Penghasilan per Bulan', 'select', false, '', $income),

            self::formSection('f_sec_guardian', 'Data Wali (Opsional)', 'Isi hanya jika calon siswa tinggal bersama wali'),
            self::formField('f_guardian_name', null, 'Nama Lengkap Wali', 'text'),
            self::formField('f_guardian_relation', null, 'Hubungan dengan Calon Siswa', 'text', false, 'Contoh: kakek, paman, bibi'),
            self::formField('f_guardian_job', null, 'Pekerjaan Wali', 'text'),

            self::formSection('f_sec_contact', 'Kontak yang Dapat Dihubungi'),
            self::formField('f_parent_phone', 'parent_phone', 'Nomor HP / WhatsApp Aktif', 'text', true, 'Nomor yang dapat dihubungi untuk informasi PPDB'),
            self::formField('f_parent_email', 'parent_email', 'Email', 'text', false, 'Opsional'),
        ];
    }

    /** Template "Riwayat Pendidikan" + "Prestasi Akademik dan Non Akademik". */
    public static function educationFormFields(): array
    {
        $levels = ['PAUD / TK / RA', 'SD / MI', 'SMP / MTs', 'SMA / MA / SMK', 'Lainnya'];
        $f = [];

        foreach ([1, 2, 3] as $i) {
            $f[] = self::formSection("f_edu{$i}_sec", "Riwayat Pendidikan {$i}", $i === 1 ? 'Isi jenjang sekolah sebelumnya. Kosongkan jika tidak ada' : '');
            $f[] = self::formField("f_edu{$i}_level", null, 'Tingkat', 'select', false, '', $levels);
            $f[] = self::formField("f_edu{$i}_school", null, 'Nama Sekolah', 'text');
            $f[] = self::formField("f_edu{$i}_address", null, 'Alamat Sekolah', 'text');
            $f[] = self::formField("f_edu{$i}_phone", null, 'Telepon Sekolah', 'text');
            $f[] = self::formField("f_edu{$i}_year", null, 'Tahun Lulus', 'number', false, 'Contoh: 2024');
        }

        foreach (['acad' => 'Prestasi Akademik', 'nonacad' => 'Prestasi Non Akademik'] as $key => $title) {
            foreach ([1, 2] as $i) {
                $f[] = self::formSection("f_{$key}{$i}_sec", "{$title} {$i}", $i === 1 ? 'Kosongkan jika belum ada prestasi' : '');
                $f[] = self::formField("f_{$key}{$i}_name", null, 'Prestasi yang Diraih', 'text');
                $f[] = self::formField("f_{$key}{$i}_class", null, 'Kelas', 'text');
                $f[] = self::formField("f_{$key}{$i}_year", null, 'Tahun', 'number');
                $f[] = self::formField("f_{$key}{$i}_note", null, 'Keterangan', 'text');
            }
        }

        return $f;
    }

    /** Template "Riwayat Kesehatan": pertumbuhan jasmani + penyakit yang pernah diderita. */
    public static function healthFormFields(): array
    {
        $normal = fn (string $other) => ['Normal', $other];

        $f = [
            self::formSection('f_sec_body', 'Pertumbuhan Jasmani dan Kesehatan', 'Keadaan jasmani calon peserta didik'),
            self::formField('f_blood', null, 'Golongan Darah', 'select', false, '', ['A', 'B', 'AB', 'O', 'Belum diketahui']),
            self::formField('f_height', null, 'Tinggi Badan (cm)', 'number'),
            self::formField('f_weight', null, 'Berat Badan (kg)', 'number'),
            self::formField('f_sight', null, 'Penglihatan', 'select', false, '', ['Normal', 'Berkacamata', 'Gangguan penglihatan lainnya']),
            self::formField('f_hearing', null, 'Pendengaran', 'select', false, '', $normal('Ada gangguan pendengaran')),
            self::formField('f_speech', null, 'Pembicaraan', 'select', false, '', $normal('Ada gangguan bicara')),
            self::formField('f_body_other', null, 'Keadaan Jasmani Lainnya', 'paragraph', false, 'Contoh: alergi, cacat bawaan, kebutuhan khusus'),
        ];

        foreach ([1, 2, 3] as $i) {
            $f[] = self::formSection("f_ill{$i}_sec", "Penyakit yang Pernah Diderita {$i}", $i === 1 ? 'Kosongkan jika tidak pernah menderita penyakit serius' : '');
            $f[] = self::formField("f_ill{$i}_name", null, 'Nama Penyakit', 'text');
            $f[] = self::formField("f_ill{$i}_year", null, 'Tahun', 'number');
            $f[] = self::formField("f_ill{$i}_long", null, 'Lamanya', 'text', false, 'Contoh: 2 minggu');
            $f[] = self::formField("f_ill{$i}_note", null, 'Keterangan', 'text');
        }

        return $f;
    }

    /** Template "Lain-Lain": ibadah di rumah + menu makanan. */
    public static function otherFormFields(): array
    {
        $freq = ['Setiap hari', 'Kadang-kadang', 'Jarang'];

        return [
            self::formSection('f_sec_worship', 'Ibadah Peserta Didik di Rumah'),
            self::formField('f_worship_salat', null, 'Shalat', 'radio', false, '', ['Lengkap 5 waktu', 'Tidak lengkap']),
            self::formField('f_worship_quran', null, "Membaca Al-Qur'an / Iqro", 'radio', false, '', $freq),
            self::formField('f_worship_zikir', null, 'Zikir Sesudah Shalat', 'radio', false, '', $freq),

            self::formSection('f_sec_food', 'Menu Makanan'),
            self::formField('f_food_like', null, 'Menu Makanan yang Paling Disukai', 'text'),
            self::formField('f_food_dislike', null, 'Menu Makanan yang Tidak Disukai', 'text'),
            self::formField('f_food_veg', null, 'Sayur', 'radio', false, '', ['Suka', 'Tidak suka']),
        ];
    }

    /** Teks "satu per baris" menjadi array. */
    public static function lines(?string $text): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $text))));
    }

    /** Isi ppdb_info menjadi bagian-bagian: baris "## Judul" = judul, baris lain = poin. */
    public function ppdbSections(): array
    {
        $sections = [];
        $current = null;

        foreach (self::lines($this->ppdb_info) as $line) {
            if (str_starts_with($line, '## ')) {
                $sections[] = ['title' => trim(substr($line, 3)), 'items' => []];
                $current = array_key_last($sections);
            } else {
                if ($current === null) {
                    $sections[] = ['title' => null, 'items' => []];
                    $current = array_key_last($sections);
                }
                $sections[$current]['items'][] = $line;
            }
        }

        return $sections;
    }

    public function missionList(): array
    {
        return self::lines($this->mission);
    }

    public function goalsList(): array
    {
        return self::lines($this->goals);
    }

    public function logoUrl(): ?string
    {
        return $this->logo ? asset('storage/'.$this->logo) : null;
    }

    public function headPhotoUrl(): ?string
    {
        return $this->head_photo ? asset('storage/'.$this->head_photo) : null;
    }

    public function structureImageUrl(): ?string
    {
        return $this->structure_image ? asset('storage/'.$this->structure_image) : null;
    }

    public function aboutPhotos(): HasMany
    {
        return $this->hasMany(AboutPhoto::class)->orderBy('sort')->orderBy('id');
    }

    public function facilities(): HasMany
    {
        return $this->hasMany(Facility::class)->orderBy('sort');
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(Milestone::class);
    }

    /** Poster PPDB yang diunggah admin / super admin. */
    public function ppdbPosterUrl(): ?string
    {
        return $this->ppdb_poster ? asset('storage/'.$this->ppdb_poster) : null;
    }

    public function safeColor(): string
    {
        return preg_match('/^#[0-9a-fA-F]{6}$/', (string) $this->primary_color) ? $this->primary_color : '#0f766e';
    }

    public function ppdbYear(): int
    {
        return (int) ($this->ppdb_year ?: now()->year);
    }

    /** Pendaftaran terbuka bila saklar aktif dan hari ini berada dalam rentang tanggal (jika diisi). */
    public function ppdbIsOpen(): bool
    {
        if (! $this->ppdb_open) {
            return false;
        }

        $today = today();

        if ($this->ppdb_start && $today->lt($this->ppdb_start)) {
            return false;
        }

        if ($this->ppdb_end && $today->gt($this->ppdb_end)) {
            return false;
        }

        return true;
    }
}