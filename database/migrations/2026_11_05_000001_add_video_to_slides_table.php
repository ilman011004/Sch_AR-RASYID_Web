<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('slides', function (Blueprint $t) {
            $t->string('type', 10)->default('foto')->after('school_id'); // foto | video
            $t->string('video')->nullable()->after('image');
            $t->string('image')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('slides', function (Blueprint $t) {
            $t->dropColumn(['type', 'video']);
        });
    }
};