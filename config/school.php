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

    // Foto slider di beranda sekolah
    'slider' => [
        'max_photos' => env('SLIDER_MAX_PHOTOS', 3),
        'max_size_kb' => 3072, // 3 MB
        'interval_ms' => 5000,
    ],

    // Slug yang tidak boleh dipakai sekolah
    'reserved_slugs' => [
        'admin', 'login', 'storage', 'up', 'css', 'js', 'build', 'vendor', 'api', 'www',
        'yayasan', 'profil', 'berita', 'spmb', 'jenjang',
    ],
];