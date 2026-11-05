<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $t) {
            $t->unsignedTinyInteger('stage')->default(1)->after('reg_number'); // 1 berkas, 2 seleksi, 3 wawancara, 4 selesai
            $t->boolean('docs_verified')->default(false)->after('stage');
            $t->decimal('test_score', 5, 2)->nullable()->after('docs_verified');
            $t->text('test_note')->nullable()->after('test_score');
            $t->dateTime('interview_at')->nullable()->after('test_note');
            $t->string('interview_place')->nullable()->after('interview_at');
            $t->text('interview_note')->nullable()->after('interview_place');
        });

        // Status disederhanakan: diproses | diterima | ditolak
        DB::table('registrations')->whereIn('status', ['baru', 'cadangan'])->update(['status' => 'diproses']);
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $t) {
            $t->dropColumn(['stage', 'docs_verified', 'test_score', 'test_note', 'interview_at', 'interview_place', 'interview_note']);
        });
    }
};