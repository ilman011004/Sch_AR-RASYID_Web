<?php

return [
    // 'path'      => http://domain/sd, http://domain/smp   (cocok untuk pengembangan lokal)
    // 'subdomain' => http://sd.domain, http://smp.domain   (untuk produksi)
    'routing' => env('SCHOOL_ROUTING', 'path'),

    // Domain utama tanpa "http://", contoh: arrasyid.sch.id (hanya dipakai bila routing = subdomain)
    'base_domain' => env('SCHOOL_BASE_DOMAIN', 'localhost'),

    'foundation_name' => env('FOUNDATION_NAME', 'Yayasan AR-Rasyid'),

    // Slug baris yayasan di tabel schools (level = yayasan)
    'foundation_slug' => 'yayasan',

    // Zona waktu dan bahasa tanggal. WITA = Asia/Makassar, WIB = Asia/Jakarta, WIT = Asia/Jayapura
    'timezone' => env('SCHOOL_TIMEZONE', 'Asia/Makassar'),
    'locale' => env('SCHOOL_LOCALE', 'id'),

    // Lokasi peta. Satu lokasi dipakai oleh yayasan dan semua jenjang.
    'map' => [
        'name' => env('SCHOOL_MAP_NAME', 'Kampus AR-Rasyid'),
        'address' => env('SCHOOL_MAP_ADDRESS', 'FXPG+V4W, Segumbang, Batulicin, Tanah Bumbu Regency, South Kalimantan 72273'),
        'query' => env('SCHOOL_MAP_QUERY', 'FXPG+V4W, Segumbang, Batulicin, Tanah Bumbu Regency, South Kalimantan 72273'),
    ],

    // Jam operasional (satu baris per kelompok hari). Pisahkan baris dengan tanda | di .env.
    // GANTI sesuai jam sebenarnya.
    'hours' => str_replace('|', "\n", env('SCHOOL_HOURS', 'Senin – Jumat: 07.00 – 15.00|Sabtu – Minggu: Tutup')),

    // Akun super admin pertama, dibuat oleh `php artisan db:seed`
    'seed_admin_name' => env('ADMIN_NAME', 'Super Admin'),
    'seed_admin_email' => env('ADMIN_EMAIL'),
    'seed_admin_password' => env('ADMIN_PASSWORD'),

    // Jenjang pendidikan (yayasan sengaja tidak dimasukkan di sini)
    'levels' => [
        'paud' => 'PAUD',
        'sd' => 'SD',
        'smp' => 'SMP',
        'sma' => 'SMA',
    ],

    'head_titles' => [
        'yayasan' => 'Ketua Yayasan',
        'paud' => 'Ketua PAUD',
        'sd' => 'Kepala Sekolah',
        'smp' => 'Kepala Sekolah',
        'sma' => 'Kepala Sekolah',
    ],

    'roles' => [
        'super_admin' => 'Super Admin (Yayasan)',
        'admin' => 'Admin Sekolah',
        'operator' => 'Operator / Guru',
    ],

    'ppdb_statuses' => [
        'baru' => 'Baru masuk',
        'diproses' => 'Sedang diproses',
        'diterima' => 'Diterima',
        'cadangan' => 'Cadangan',
        'ditolak' => 'Tidak diterima',
    ],

    // Slug yang tidak boleh dipakai sekolah
    'reserved_slugs' => [
        'admin', 'login', 'storage', 'up', 'css', 'js', 'build', 'vendor', 'api', 'www',
        'yayasan', 'profil', 'berita', 'spmb', 'jenjang',
    ],
];