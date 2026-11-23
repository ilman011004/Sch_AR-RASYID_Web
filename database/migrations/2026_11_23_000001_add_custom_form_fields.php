<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Menyimpan konfigurasi halaman & pertanyaan dinamis per sekolah/jenjang
        if (! Schema::hasColumn('schools', 'form_schema')) {
            Schema::table('schools', function (Blueprint $t) {
                $t->json('form_schema')->nullable()->after('ppdb_info');
            });
        }

        // Menyimpan jawaban kustom dari pendaftar
        if (! Schema::hasColumn('registrations', 'custom_fields')) {
            Schema::table('registrations', function (Blueprint $t) {
                $t->json('custom_fields')->nullable()->after('previous_school');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('schools', 'form_schema')) {
            Schema::table('schools', function (Blueprint $t) {
                $t->dropColumn('form_schema');
            });
        }

        if (Schema::hasColumn('registrations', 'custom_fields')) {
            Schema::table('registrations', function (Blueprint $t) {
                $t->dropColumn('custom_fields');
            });
        }
    }
};
