<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('schools', 'operating_hours')) {
            return;
        }

        Schema::table('schools', function (Blueprint $table) {
            $table->text('operating_hours')->nullable()->after('whatsapp');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('schools', 'operating_hours')) {
            return;
        }

        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn('operating_hours');
        });
    }
};