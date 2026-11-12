<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    if (! Schema::hasColumn('slides', 'video_url')) {
        Schema::table('slides', function (Blueprint $t) {
            $t->string('video_url')->nullable()->after('video');
        });
    }
}

public function down(): void
{
    if (Schema::hasColumn('slides', 'video_url')) {
        Schema::table('slides', function (Blueprint $t) {
            $t->dropColumn('video_url');
        });
    }
}
};
