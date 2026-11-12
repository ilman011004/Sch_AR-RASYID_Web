<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Admin\RegistrationController;
use App\Http\Controllers\Admin\ResourceController;
use App\Http\Controllers\Admin\SchoolController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SliderPhotoController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\PpdbAuthController;
use App\Http\Controllers\PpdbController;
use App\Http\Controllers\SiteController;
use App\Http\Middleware\EnsureRole;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Panel admin  (/admin)  — harus didaftarkan SEBELUM rute sekolah
|--------------------------------------------------------------------------
*/
// Nama 'login' wajib ada: dipakai middleware `auth` untuk mengarahkan tamu.
Route::get('admin/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('admin/login', [AuthController::class, 'login'])->middleware('throttle:6,1')->name('admin.login.submit');

Route::middleware(['auth', EnsureRole::class.':super_admin,admin,operator'])
    ->prefix('admin')->name('admin.')->group(function () {

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');

        // Modul CRUD (berita, galeri, guru, program, keunggulan, faq) — izin per modul di config/admin.php
        Route::prefix('r/{resource}')->where(['resource' => '[a-z]+'])->name('resource.')->group(function () {
            Route::get('/', [ResourceController::class, 'index'])->name('index');
            Route::get('create', [ResourceController::class, 'create'])->name('create');
            Route::post('/', [ResourceController::class, 'store'])->name('store');
            Route::get('{id}/edit', [ResourceController::class, 'edit'])->whereNumber('id')->name('edit');
            Route::put('{id}', [ResourceController::class, 'update'])->whereNumber('id')->name('update');
            Route::delete('{id}', [ResourceController::class, 'destroy'])->whereNumber('id')->name('destroy');
        });

        // Admin sekolah + super admin
        Route::middleware(EnsureRole::class.':super_admin,admin')->group(function () {
            Route::get('pengaturan', [SettingsController::class, 'edit'])->name('settings');
            Route::put('pengaturan', [SettingsController::class, 'update'])->name('settings.update');

            Route::get('ppdb', [RegistrationController::class, 'index'])->name('ppdb.index');
            Route::get('ppdb/export', [RegistrationController::class, 'export'])->name('ppdb.export');
            Route::get('ppdb/{id}', [RegistrationController::class, 'show'])->whereNumber('id')->name('ppdb.show');
            Route::put('ppdb/{id}', [RegistrationController::class, 'update'])->whereNumber('id')->name('ppdb.update');
            Route::delete('ppdb/{id}', [RegistrationController::class, 'destroy'])->whereNumber('id')->name('ppdb.destroy');
            Route::get('ppdb/{id}/dokumen/{field}', [RegistrationController::class, 'document'])
                ->whereNumber('id')->name('ppdb.document');

            // Pesan kontak: dibatasi per jenjang di MessageController (AdminContext::schoolId())
            Route::get('pesan', [MessageController::class, 'index'])->name('messages.index');
            Route::post('pesan/{id}/baca', [MessageController::class, 'read'])->whereNumber('id')->name('messages.read');
            Route::post('pesan/{id}/balas', [MessageController::class, 'reply'])->whereNumber('id')->name('messages.reply');
            Route::delete('pesan/{id}', [MessageController::class, 'destroy'])->whereNumber('id')->name('messages.destroy');

            Route::get('akun', [UserController::class, 'index'])->name('users.index');
            Route::get('akun/create', [UserController::class, 'create'])->name('users.create');
            Route::post('akun', [UserController::class, 'store'])->name('users.store');
            Route::get('akun/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
            Route::put('akun/{user}', [UserController::class, 'update'])->name('users.update');
            Route::delete('akun/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        });

        // Khusus super admin (yayasan)
        Route::middleware(EnsureRole::class.':super_admin')->group(function () {
            Route::post('pilih-sekolah', [DashboardController::class, 'switchSchool'])->name('switch-school');

            // Pendaftar semua jenjang (PAUD sampai SMA)
            Route::get('semua-pendaftar', [RegistrationController::class, 'all'])->name('ppdb.all');
            Route::get('semua-pendaftar/export', [RegistrationController::class, 'exportAll'])->name('ppdb.all.export');

            Route::get('sekolah', [SchoolController::class, 'index'])->name('schools.index');
            Route::get('sekolah/create', [SchoolController::class, 'create'])->name('schools.create');
            Route::post('sekolah', [SchoolController::class, 'store'])->name('schools.store');
            Route::get('sekolah/{managed}/edit', [SchoolController::class, 'edit'])->name('schools.edit');
            Route::put('sekolah/{managed}', [SchoolController::class, 'update'])->name('schools.update');
        });
    });

// Slider foto. Dibatasi role admin supaya akun orang tua tidak bisa masuk.
Route::middleware(['auth', EnsureRole::class.':super_admin,admin,operator'])
    ->prefix('admin/{school}/slider')->name('admin.slider.')->group(function () {
        Route::get('/', [SliderPhotoController::class, 'index'])->name('index');
        Route::post('/', [SliderPhotoController::class, 'store'])->name('store');
        Route::put('/{photo}', [SliderPhotoController::class, 'update'])->name('update');
        Route::delete('/{photo}', [SliderPhotoController::class, 'destroy'])->name('destroy');
    });

/*
|--------------------------------------------------------------------------
| Situs induk yayasan (domain utama)
|--------------------------------------------------------------------------
| Harus didaftarkan SEBELUM rute sekolah, supaya /berita tidak dianggap
| sebagai sekolah bernama "berita".
*/
$portalRoutes = function () {
    Route::get('/', [PortalController::class, 'home'])->name('portal');
    Route::get('/profil/{page}', [PortalController::class, 'profile'])
        ->whereIn('page', ['struktur', 'visi-misi', 'sejarah'])->name('portal.profile');
    Route::get('/spmb/pendaftaran', [PortalController::class, 'spmb'])->name('portal.spmb');
    Route::get('/spmb/pengumuman', [PortalController::class, 'announcements'])->name('portal.announcements');
    Route::get('/berita', [PortalController::class, 'news'])->name('portal.news');
    Route::get('/berita/{slug}', [PortalController::class, 'newsShow'])->name('portal.news.show');
};

// Mode subdomain: situs yayasan hanya di domain utama, supaya tidak bentrok dengan sd.domain/berita.
if (config('school.routing') === 'subdomain') {
    Route::domain(config('school.base_domain'))->group($portalRoutes);
} else {
    $portalRoutes();
}

/*
|--------------------------------------------------------------------------
| Situs publik tiap jenjang
|--------------------------------------------------------------------------
| SCHOOL_ROUTING=path      -> /sd, /smp, ...        (lokal)
| SCHOOL_ROUTING=subdomain -> sd.domain, smp.domain (produksi)
| Nama rute sama di kedua mode, jadi tampilan tidak perlu diubah.
*/
$schoolRoutes = function () {
    Route::get('/', [SiteController::class, 'home'])->name('home');

    // Menu Profil (dropdown): sejarah, visi-misi (termasuk tujuan), struktur, pegawai
    Route::get('/profil', [SiteController::class, 'profileIndex'])->name('profile.index');
    Route::get('/profil/{page}', [SiteController::class, 'profile'])
        ->whereIn('page', ['sejarah', 'visi-misi', 'struktur', 'pegawai'])->name('profile');

    // Menu Program Akademik (dropdown): unggulan, kurikulum, ekstrakurikuler
    Route::get('/program-akademik', [SiteController::class, 'programsIndex'])->name('programs.index');
    Route::get('/program-akademik/{page}', [SiteController::class, 'programs'])
        ->whereIn('page', ['unggulan', 'kurikulum', 'ekstrakurikuler'])->name('programs');

    Route::get('/berita', [SiteController::class, 'news'])->name('news');
    Route::get('/berita/{slug}', [SiteController::class, 'newsShow'])->name('news.show');
    Route::get('/galeri', [SiteController::class, 'gallery'])->name('gallery');

    // Kontak: kirim pesan wajib login (dicek di SiteController, karena login per jenjang lewat ppdb.login)
    Route::get('/kontak', [SiteController::class, 'contact'])->name('contact');
    Route::post('/kontak', [SiteController::class, 'contactStore'])->middleware('throttle:5,10')->name('contact.store');

    // --- PPDB: halaman publik (poster + informasi + cek status) ---
    Route::get('/ppdb', [PpdbController::class, 'index'])->name('ppdb');
    Route::get('/ppdb/cek-status', [PpdbController::class, 'status'])->name('ppdb.status');
    Route::post('/ppdb/cek-status', [PpdbController::class, 'check'])->middleware('throttle:10,1')->name('ppdb.check');

    // --- PPDB: akun pendaftar (orang tua / wali) ---
    Route::get('/ppdb/masuk', [PpdbAuthController::class, 'showLogin'])->name('ppdb.login');
    Route::post('/ppdb/masuk', [PpdbAuthController::class, 'login'])->middleware('throttle:6,1')->name('ppdb.login.submit');
    Route::get('/ppdb/daftar-akun', [PpdbAuthController::class, 'showRegister'])->name('ppdb.register');
    Route::post('/ppdb/daftar-akun', [PpdbAuthController::class, 'register'])->middleware('throttle:5,10')->name('ppdb.register.submit');

    // Verifikasi email dengan kode OTP (akun dibuat setelah kode benar)
    Route::get('/ppdb/verifikasi', [PpdbAuthController::class, 'showVerify'])->name('ppdb.verify');
    Route::post('/ppdb/verifikasi', [PpdbAuthController::class, 'verify'])->middleware('throttle:10,10')->name('ppdb.verify.submit');
    Route::post('/ppdb/verifikasi/kirim-ulang', [PpdbAuthController::class, 'resend'])->middleware('throttle:5,10')->name('ppdb.verify.resend');

      // Keluar: cukup sudah login, role apa pun boleh logout
    Route::post('/ppdb/keluar', [PpdbAuthController::class, 'logout'])->middleware('auth')->name('ppdb.logout');

    // --- PPDB: wajib login sebagai orang tua / wali ---
    Route::middleware(['auth', EnsureRole::class.':parent'])->group(function () {
        Route::get('/ppdb/formulir', [PpdbController::class, 'form'])->name('ppdb.form');
        Route::post('/ppdb/formulir', [PpdbController::class, 'store'])->middleware('throttle:5,10')->name('ppdb.store');
        Route::get('/ppdb/berhasil', [PpdbController::class, 'success'])->name('ppdb.success');
    });
};

if (config('school.routing') === 'subdomain') {
    Route::domain('{school}.'.config('school.base_domain'))
        ->where(['school' => '[a-z0-9-]+'])
        ->group($schoolRoutes);
} else {
    Route::prefix('{school}')
        ->where(['school' => '[a-z0-9-]+'])
        ->group($schoolRoutes);
}