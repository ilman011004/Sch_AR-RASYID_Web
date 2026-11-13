<?php

use App\Models\AboutPhoto;
use App\Models\Advantage;
use App\Models\Facility; // BARU
use App\Models\Faq;
use App\Models\Gallery;
use App\Models\Milestone;
use App\Models\Post;
use App\Models\Program;
use App\Models\Slide;
use App\Models\Staff;

/*
 | Definisi panel admin.
 |
 | 'resources'     : modul CRUD sederhana (satu pengendali dipakai untuk semuanya).
 | 'school_fields' : isi halaman "Pengaturan Sekolah".
 |
 | Tipe field: text, textarea, select, image, video, date, datetime, number, checkbox, color, url
 | Opsi tambahan: 'button' (teks tombol upload), 'show_if' => ['nama_field' => 'nilai'] (tampil hanya bila field lain bernilai itu)
 | BARU (modul): 'only' => 'foundation' (menu hanya ada saat yayasan yang dikelola)
 | BARU (khusus school_fields): 'only' => 'foundation' (hanya tampil untuk yayasan),
 |                              'except' => 'foundation' (tampil untuk semua kecuali yayasan),
 |                              'roles' => ['super_admin'] (hanya peran tersebut yang boleh melihat & mengubah)
 | Semua aturan ini ditegakkan di server (SettingsController), bukan hanya disembunyikan di tampilan.
 */

return [

    'resources' => [

        // DIUBAH: slider kini mendukung link YouTube (field video_url)
        'slider' => [
            'label' => 'Slider Beranda',
            'model' => Slide::class,
            'dir' => 'slides',
            'roles' => ['super_admin', 'admin'],
            'columns' => ['type' => 'Jenis', 'sort' => 'Urutan'],
            'order' => ['sort', 'asc'],
            'fields' => [
                ['name' => 'type', 'label' => 'Jenis slide', 'type' => 'select', 'rules' => 'required|in:foto,video',
                    'options' => ['foto' => 'Foto', 'video' => 'Video']],
                ['name' => 'image', 'label' => 'Foto', 'type' => 'image', 'button' => 'Pilih Foto',
                    'help' => 'Pilih foto mendatar (lebar) agar tampil penuh. Untuk slide video, foto ini dipakai sebagai gambar pembuka (boleh dikosongkan).'],
                ['name' => 'video_url', 'label' => 'Link video YouTube', 'type' => 'url',
                    'rules' => ['nullable', 'url:https', 'max:255', 'regex:~(youtu\.be/|youtube\.com/)~i'],
                    'show_if' => ['type' => 'video'],
                    'help' => 'Tempel link YouTube, contoh: https://www.youtube.com/watch?v=xxxxxxxxxxx. Cara termudah, tanpa upload. Jika diisi, link ini dipakai dan berkas video di bawah diabaikan.'],
                ['name' => 'video', 'label' => 'Atau unggah berkas video', 'type' => 'video', 'max_kb' => 51200, 'button' => 'Pilih Video',
                    'show_if' => ['type' => 'video'],
                    'help' => 'Opsional. Hanya dipakai bila link YouTube kosong. Video diputar tanpa suara. MP4/WebM, maks. 50 MB.'],
                ['name' => 'sort', 'label' => 'Urutan', 'type' => 'number', 'rules' => 'nullable|integer|min:0|max:9999', 'default' => 0,
                    'help' => 'Angka kecil tampil lebih dulu.'],
            ],
        ],

        'berita' => [
            'label' => 'Berita & Pengumuman',
            'model' => Post::class,
            'dir' => 'posts',
            'roles' => ['super_admin', 'admin', 'operator'],
            'own_only' => true, // operator hanya melihat/mengubah tulisan miliknya
            'columns' => ['title' => 'Judul', 'type' => 'Jenis', 'status' => 'Status', 'published_at' => 'Tayang'],
            'order' => ['created_at', 'desc'],
            'fields' => [
                ['name' => 'title', 'label' => 'Judul', 'type' => 'text', 'rules' => 'required|string|max:200'],
                ['name' => 'type', 'label' => 'Jenis', 'type' => 'select', 'rules' => 'required|in:berita,pengumuman',
                    'options' => ['berita' => 'Berita kegiatan', 'pengumuman' => 'Pengumuman penting']],
                ['name' => 'excerpt', 'label' => 'Ringkasan', 'type' => 'textarea', 'rows' => 3, 'rules' => 'nullable|string|max:300',
                    'help' => 'Maksimal 300 karakter. Tampil di daftar berita.'],
                ['name' => 'body', 'label' => 'Isi', 'type' => 'textarea', 'rows' => 12, 'rules' => 'required|string|max:20000',
                    'help' => 'Teks biasa. Pisahkan paragraf dengan baris kosong.'],
                ['name' => 'image', 'label' => 'Foto sampul', 'type' => 'image'],
                ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'rules' => 'required|in:draft,pending,published',
                    'options' => ['draft' => 'Draf', 'pending' => 'Menunggu persetujuan', 'published' => 'Terbitkan'],
                    'help' => 'Operator: pilihan "Terbitkan" akan menjadi "Menunggu persetujuan" sampai disetujui admin sekolah.'],
                ['name' => 'published_at', 'label' => 'Tanggal tayang', 'type' => 'datetime', 'rules' => 'nullable|date',
                    'help' => 'Kosongkan untuk memakai waktu saat ini.'],
            ],
        ],

        'galeri' => [
            'label' => 'Galeri',
            'model' => Gallery::class,
            'dir' => 'galleries',
            'roles' => ['super_admin', 'admin', 'operator'],
            'columns' => ['title' => 'Judul', 'category' => 'Kategori', 'created_at' => 'Diunggah'],
            'order' => ['created_at', 'desc'],
            'fields' => [
                ['name' => 'title', 'label' => 'Judul', 'type' => 'text', 'rules' => 'required|string|max:150'],
                ['name' => 'category', 'label' => 'Kategori', 'type' => 'select', 'rules' => 'required|in:pembelajaran,acara,prestasi',
                    'options' => ['pembelajaran' => 'Kegiatan pembelajaran', 'acara' => 'Acara sekolah', 'prestasi' => 'Perlombaan & prestasi']],
                ['name' => 'image', 'label' => 'Foto', 'type' => 'image', 'required_on_create' => true],
                ['name' => 'caption', 'label' => 'Keterangan', 'type' => 'text', 'rules' => 'nullable|string|max:255'],
            ],
        ],

        'guru' => [
            'label' => 'Pegawai (Guru & Staf)',
            'model' => Staff::class,
            'dir' => 'staff',
            'roles' => ['super_admin', 'admin'],
            'columns' => ['name' => 'Nama', 'position' => 'Jabatan', 'staff_group' => 'Kelompok', 'sort' => 'Urutan'],
            'order' => ['sort', 'asc'],
            'fields' => [
                ['name' => 'photo', 'label' => 'Foto', 'type' => 'image',
                    'help' => 'Disarankan foto persegi (rasio 1:1).'],
                ['name' => 'name', 'label' => 'Nama (beserta gelar)', 'type' => 'text', 'rules' => 'required|string|max:150'],
                ['name' => 'position', 'label' => 'Jabatan', 'type' => 'text', 'rules' => 'required|string|max:150',
                    'help' => 'Contoh: Guru Kelas 1, Tata Usaha, Wakil Kepala Sekolah.'],
                ['name' => 'subject', 'label' => 'Mata pelajaran / bidang (opsional)', 'type' => 'text', 'rules' => 'nullable|string|max:150'],
                ['name' => 'staff_group', 'label' => 'Kelompok', 'type' => 'select', 'rules' => 'required|in:pendidik,kependidikan',
                    'options' => ['pendidik' => 'Tenaga pendidik (guru)', 'kependidikan' => 'Tenaga kependidikan']],
                ['name' => 'sort', 'label' => 'Urutan', 'type' => 'number', 'rules' => 'nullable|integer|min:0|max:9999', 'default' => 0,
                    'help' => 'Angka kecil tampil lebih dulu.'],
            ],
        ],

        'program' => [
            'label' => 'Program Unggulan & Ekstrakurikuler',
            'model' => Program::class,
            'dir' => 'programs',
            'roles' => ['super_admin', 'admin'],
            'columns' => ['title' => 'Nama', 'kind' => 'Jenis', 'sort' => 'Urutan'],
            'order' => ['sort', 'asc'],
            'fields' => [
                ['name' => 'kind', 'label' => 'Jenis', 'type' => 'select', 'rules' => 'required|in:unggulan,ekstrakurikuler',
                    'options' => ['unggulan' => 'Program unggulan', 'ekstrakurikuler' => 'Ekstrakurikuler']],
                ['name' => 'title', 'label' => 'Nama', 'type' => 'text', 'rules' => 'required|string|max:150'],
                ['name' => 'description', 'label' => 'Deskripsi', 'type' => 'textarea', 'rules' => 'nullable|string|max:2000'],
                ['name' => 'image', 'label' => 'Foto', 'type' => 'image'],
                ['name' => 'sort', 'label' => 'Urutan', 'type' => 'number', 'rules' => 'nullable|integer|min:0|max:9999', 'default' => 0],
            ],
        ],

        'tonggak' => [
            'label' => 'Tonggak Sejarah',
            'model' => Milestone::class,
            'dir' => 'milestones',
            'roles' => ['super_admin', 'admin'],
            'columns' => ['year' => 'Tahun', 'title' => 'Judul', 'sort' => 'Urutan'],
            'order' => ['sort', 'asc'],
            'fields' => [
                ['name' => 'year', 'label' => 'Tahun', 'type' => 'text', 'rules' => 'required|string|max:20',
                    'help' => 'Contoh: 2012 atau 2013-2014.'],
                ['name' => 'title', 'label' => 'Judul', 'type' => 'text', 'rules' => 'required|string|max:150',
                    'help' => 'Contoh: Berdiri dengan program TPA.'],
                ['name' => 'description', 'label' => 'Keterangan singkat', 'type' => 'textarea', 'rows' => 3,
                    'rules' => 'nullable|string|max:1000'],
                ['name' => 'photo', 'label' => 'Foto (opsional)', 'type' => 'image', 'button' => 'Pilih Foto',
                    'help' => 'Disarankan foto mendatar. Boleh dikosongkan.'],
                ['name' => 'sort', 'label' => 'Urutan', 'type' => 'number', 'rules' => 'nullable|integer|min:0|max:9999', 'default' => 0,
                    'help' => 'Angka kecil tampil lebih dulu. Isi 1, 2, 3, dst. sesuai urutan kejadian.'],
            ],
        ],

        'keunggulan' => [
            'label' => 'Keunggulan Sekolah',
            'model' => Advantage::class,
            'dir' => 'advantages',
            'roles' => ['super_admin', 'admin'],
            'columns' => ['icon' => 'Ikon', 'title' => 'Judul', 'sort' => 'Urutan'],
            'order' => ['sort', 'asc'],
            'fields' => [
                ['name' => 'icon', 'label' => 'Ikon (emoji)', 'type' => 'text', 'rules' => 'nullable|string|max:10', 'help' => 'Contoh: 📚 🏆 🌱'],
                ['name' => 'title', 'label' => 'Judul', 'type' => 'text', 'rules' => 'required|string|max:150'],
                ['name' => 'description', 'label' => 'Deskripsi', 'type' => 'textarea', 'rules' => 'nullable|string|max:1000'],
                ['name' => 'sort', 'label' => 'Urutan', 'type' => 'number', 'rules' => 'nullable|integer|min:0|max:9999', 'default' => 0],
            ],
        ],

        // BARU: modul Fasilitas Sekolah
        'fasilitas' => [
            'label' => 'Fasilitas Sekolah',
            'model' => Facility::class,
            'dir' => 'facilities',
            'roles' => ['super_admin', 'admin'],
            'columns' => ['name' => 'Nama', 'sort' => 'Urutan'],
            'order' => ['sort', 'asc'],
            'fields' => [
                ['name' => 'name', 'label' => 'Nama fasilitas', 'type' => 'text', 'rules' => 'required|string|max:150',
                    'help' => 'Contoh: Perpustakaan, Laboratorium, Masjid.'],
                ['name' => 'description', 'label' => 'Keterangan singkat', 'type' => 'textarea', 'rows' => 3,
                    'rules' => 'nullable|string|max:300'],
                ['name' => 'image', 'label' => 'Foto', 'type' => 'image', 'button' => 'Pilih Foto', 'required_on_create' => true,
                    'help' => 'Foto mendatar atau tegak sama-sama bisa, letakkan objek utama di tengah. Maks. 2 MB.'],
                ['name' => 'sort', 'label' => 'Urutan', 'type' => 'number', 'rules' => 'nullable|integer|min:0|max:9999', 'default' => 0,
                    'help' => 'Angka kecil tampil lebih dulu.'],
            ],
        ],

        // BARU: foto-foto bagian "Tentang Yayasan" di beranda portal. Menu sendiri, khusus super admin,
        // dan hanya muncul saat yang dikelola adalah yayasan. (Kunci modul wajib huruf a-z saja.)
        'fotoyayasan' => [
            'label' => 'Foto Tentang Yayasan',
            'model' => AboutPhoto::class,
            'dir' => 'about',
            'roles' => ['super_admin'],
            'only' => 'foundation',
            'columns' => ['title' => 'Keterangan foto', 'sort' => 'Urutan'],
            'order' => ['sort', 'asc'],
            'fields' => [
                ['name' => 'image', 'label' => 'Foto', 'type' => 'image', 'button' => 'Pilih Foto', 'required_on_create' => true,
                    'help' => 'Foto mendatar atau tegak sama-sama bisa, tampil utuh tanpa dipotong. Maks. 2 MB. Foto tampil sebagai slider di beranda (maks. 10 foto pertama).'],
                ['name' => 'title', 'label' => 'Keterangan foto', 'type' => 'text', 'rules' => 'required|string|max:120',
                    'help' => 'Singkat saja, contoh: Gedung SD Islam Terpadu. Tampil sebagai teks di bagian bawah foto.'],
                ['name' => 'sort', 'label' => 'Urutan', 'type' => 'number', 'rules' => 'nullable|integer|min:0|max:9999', 'default' => 0,
                    'help' => 'Angka kecil tampil lebih dulu di slider.'],
            ],
        ],

        'faq' => [
            'label' => 'FAQ',
            'model' => Faq::class,
            'dir' => 'faqs',
            'roles' => ['super_admin', 'admin'],
            'columns' => ['question' => 'Pertanyaan', 'sort' => 'Urutan'],
            'order' => ['sort', 'asc'],
            'fields' => [
                ['name' => 'question', 'label' => 'Pertanyaan', 'type' => 'text', 'rules' => 'required|string|max:255'],
                ['name' => 'answer', 'label' => 'Jawaban', 'type' => 'textarea', 'rules' => 'required|string|max:3000'],
                ['name' => 'sort', 'label' => 'Urutan', 'type' => 'number', 'rules' => 'nullable|integer|min:0|max:9999', 'default' => 0],
            ],
        ],
    ],

    'school_fields' => [

        'Identitas Sekolah' => [
            ['name' => 'name', 'label' => 'Nama sekolah', 'type' => 'text', 'rules' => 'required|string|max:150'],
            ['name' => 'tagline', 'label' => 'Judul beranda', 'type' => 'text', 'rules' => 'nullable|string|max:200',
                'help' => 'Kalimat besar di bagian atas beranda.'],
            ['name' => 'description', 'label' => 'Deskripsi singkat sekolah', 'type' => 'textarea', 'rows' => 3, 'rules' => 'nullable|string|max:1000'],
            // DIUBAH: video profil tidak dipakai di yayasan, hanya untuk jenjang sekolah
            ['name' => 'video_url', 'label' => 'Video profil (link YouTube)', 'type' => 'url',
                'rules' => ['nullable', 'url:https', 'max:255', 'regex:~(youtu\.be/|youtube\.com/)~i'],
                'except' => 'foundation',
                'help' => 'Tempel link video YouTube. Kosongkan bila tidak ada video.'],
            ['name' => 'logo', 'label' => 'Logo', 'type' => 'image'],
            ['name' => 'primary_color', 'label' => 'Warna utama', 'type' => 'color', 'rules' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/']],
            ['name' => 'founded_year', 'label' => 'Tahun berdiri', 'type' => 'number', 'rules' => 'nullable|integer|min:1800|max:2100',
                'help' => 'Dipakai untuk kartu statistik di halaman Sejarah.'],
        ],

        // BARU: hanya tampil untuk super admin saat yang dikelola adalah yayasan.
        // Foto-foto Tentang Yayasan kini punya menu sendiri: "Foto Tentang Yayasan" (modul 'fotoyayasan').
        'Tentang Yayasan' => [
            ['name' => 'about_text', 'label' => 'Teks Tentang Yayasan', 'type' => 'textarea', 'rows' => 8,
                'rules' => 'nullable|string|max:3000', 'only' => 'foundation', 'roles' => ['super_admin'],
                'help' => 'Cukup 2 sampai 3 paragraf pendek. Pisahkan paragraf dengan baris kosong. Foto diatur di menu "Foto Tentang Yayasan".'],
        ],

        'Kepala Sekolah' => [
            ['name' => 'head_title', 'label' => 'Jabatan', 'type' => 'text', 'rules' => 'required|string|max:100',
                'help' => 'Contoh: Kepala Sekolah.'],
            ['name' => 'head_name', 'label' => 'Nama', 'type' => 'text', 'rules' => 'nullable|string|max:150'],
            ['name' => 'head_photo', 'label' => 'Foto', 'type' => 'image',
                'help' => 'Disarankan foto potret (tegak), tampil di bagian Sambutan.'],
            ['name' => 'welcome_message', 'label' => 'Kata sambutan / kata inspiratif', 'type' => 'textarea', 'rows' => 6, 'rules' => 'nullable|string|max:3000'],
        ],

        'Profil' => [
            ['name' => 'history', 'label' => 'Sejarah (cerita singkat)', 'type' => 'textarea', 'rows' => 8, 'rules' => 'nullable|string|max:10000',
                'help' => 'Tampil di bawah garis waktu. Pisahkan paragraf dengan baris kosong. Tonggak per tahun diisi di menu Tonggak Sejarah.'],
            ['name' => 'vision', 'label' => 'Visi', 'type' => 'textarea', 'rows' => 3, 'rules' => 'nullable|string|max:1000'],
            ['name' => 'mission', 'label' => 'Misi', 'type' => 'textarea', 'rows' => 6, 'rules' => 'nullable|string|max:3000',
                'help' => 'Satu misi per baris.'],
            ['name' => 'goals', 'label' => 'Tujuan pendidikan', 'type' => 'textarea', 'rows' => 4, 'rules' => 'nullable|string|max:3000',
                'help' => 'Satu tujuan per baris.'],
            ['name' => 'structure_image', 'label' => 'Gambar struktur organisasi', 'type' => 'image',
                'help' => 'Unggah bagan struktur organisasi (gambar). Tampil di menu Profil → Struktur Organisasi.'],
            ['name' => 'curriculum', 'label' => 'Kurikulum yang digunakan', 'type' => 'textarea', 'rows' => 6, 'rules' => 'nullable|string|max:5000',
                'help' => 'Tampil di menu Program Akademik → Kurikulum.'],
        ],

        'Kontak & Media Sosial' => [
            ['name' => 'address', 'label' => 'Alamat', 'type' => 'textarea', 'rows' => 2, 'rules' => 'nullable|string|max:500'],
            ['name' => 'phone', 'label' => 'Telepon', 'type' => 'text', 'rules' => 'nullable|string|max:30'],
            ['name' => 'email', 'label' => 'Email', 'type' => 'text', 'rules' => 'nullable|email|max:150'],
            ['name' => 'whatsapp', 'label' => 'WhatsApp', 'type' => 'text', 'rules' => ['nullable', 'regex:/^[0-9]{8,15}$/'],
                'help' => 'Angka saja dengan kode negara, contoh: 6281234567890'],
            ['name' => 'operating_hours', 'label' => 'Jam operasional', 'type' => 'textarea', 'rows' => 4, 'rules' => 'nullable|string|max:500',
                'help' => 'Satu baris per hari. Contoh: Senin – Jumat: 07.00 – 15.00 WITA'],
            ['name' => 'facebook', 'label' => 'Facebook', 'type' => 'url', 'rules' => 'nullable|url:https|max:255'],
            ['name' => 'instagram', 'label' => 'Instagram', 'type' => 'url', 'rules' => 'nullable|url:https|max:255'],
            ['name' => 'youtube', 'label' => 'YouTube', 'type' => 'url', 'rules' => 'nullable|url:https|max:255'],
            ['name' => 'tiktok', 'label' => 'TikTok', 'type' => 'url', 'rules' => 'nullable|url:https|max:255'],
        ],

        'Statistik Beranda' => [
            ['name' => 'stat_students', 'label' => 'Total siswa aktif', 'type' => 'number', 'rules' => 'nullable|integer|min:0|max:100000'],
            ['name' => 'stat_staff', 'label' => 'Total tenaga pendidik & kependidikan', 'type' => 'number', 'rules' => 'nullable|integer|min:0|max:100000',
                'help' => 'Kosongkan agar dihitung otomatis dari daftar pegawai.'],
            ['name' => 'stat_achievements', 'label' => 'Total prestasi', 'type' => 'number', 'rules' => 'nullable|integer|min:0|max:100000',
                'help' => 'Kosongkan agar dihitung otomatis dari galeri kategori prestasi.'],
        ],

        'Pendaftaran Siswa Baru (PPDB)' => [
            ['name' => 'ppdb_open', 'label' => 'Status pendaftaran', 'type' => 'checkbox', 'rules' => 'boolean', 'help' => 'Pendaftaran dibuka'],
            ['name' => 'ppdb_year', 'label' => 'Tahun ajaran (tahun mulai)', 'type' => 'number', 'rules' => 'nullable|integer|min:2020|max:2100'],
            ['name' => 'ppdb_start', 'label' => 'Tanggal buka', 'type' => 'date', 'rules' => 'nullable|date'],
            ['name' => 'ppdb_end', 'label' => 'Tanggal tutup', 'type' => 'date', 'rules' => 'nullable|date|after_or_equal:ppdb_start'],
            ['name' => 'ppdb_poster', 'label' => 'Poster PPDB', 'type' => 'image', 'button' => 'Pilih Poster',
                'help' => 'Poster/brosur pendaftaran yang tampil di halaman PPDB. Format JPG, PNG, atau WebP, maks. 2 MB.'],
            ['name' => 'ppdb_info', 'label' => 'Informasi pendaftaran', 'type' => 'textarea', 'rows' => 12, 'rules' => 'nullable|string|max:10000',
                'help' => 'Syarat, alur, jadwal, biaya. Tulis satu poin per baris; baris diawali "## " menjadi judul bagian.'],
        ],
    ],
];