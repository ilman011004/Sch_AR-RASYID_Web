<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $t) {
            $t->dateTime('test_at')->nullable()->after('test_note');
            $t->string('test_mode', 10)->nullable()->after('test_at');   // online | offline
            $t->string('test_place')->nullable()->after('test_mode');    // lokasi atau tautan
            $t->text('test_info')->nullable()->after('test_place');      // petunjuk untuk orang tua
        });
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $t) {
            $t->dropColumn(['test_at', 'test_mode', 'test_place', 'test_info']);
        });
    }
};