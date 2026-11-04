<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $t) {
            $t->string('ppdb_poster')->nullable()->after('ppdb_info');
        });

        Schema::table('registrations', function (Blueprint $t) {
            // Pemilik pendaftaran (akun orang tua). Null untuk data lama.
            $t->foreignId('user_id')->nullable()->after('school_id')
                ->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('registrations', fn (Blueprint $t) => $t->dropConstrainedForeignId('user_id'));
        Schema::table('schools', fn (Blueprint $t) => $t->dropColumn('ppdb_poster'));
    }
};