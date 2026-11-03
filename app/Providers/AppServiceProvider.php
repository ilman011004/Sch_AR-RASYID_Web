<?php

namespace App\Providers;

use App\Models\School;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Zona waktu & bahasa diatur di sini (config/school.php).
        config(['app.timezone' => config('school.timezone')]);
        date_default_timezone_set(config('school.timezone'));
        app()->setLocale(config('school.locale'));
        Carbon::setLocale(config('school.locale'));

        // {school} di URL (/sd atau sd.domain) -> model School yang aktif, selain itu 404.
        Route::bind('school', function (string $value) {
            return School::where('slug', $value)->where('is_active', true)->firstOrFail();
        });

        // Data yang dibutuhkan semua halaman situs yayasan (menu & footer).
        View::composer(['layouts.portal', 'portal.*'], function ($view) {
            static $shared = null;

            $shared ??= [
                'foundation' => School::foundation(),
                'jenjang' => School::jenjang()->orderBy('sort')->orderBy('id')->get(),
            ];

            $view->with($shared);
        });
    }
}