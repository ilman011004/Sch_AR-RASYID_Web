<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Kolom milik tabel schools (yayasan juga satu baris di tabel ini)
        Schema::table('schools', function (Blueprint $t) {
            if (! Schema::hasColumn('schools', 'about_text')) {
                $t->text('about_text')->nullable();
            }
            if (! Schema::hasColumn('schools', 'about_photo')) {
                $t->string('about_photo')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $t) {
            foreach (['about_text', 'about_photo'] as $col) {
                if (Schema::hasColumn('schools', $col)) {
                    $t->dropColumn($col);
                }
            }
        });
    }
};