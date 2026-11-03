<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schools', function (Blueprint $t) {
            $t->id();
            $t->string('slug')->unique();
            $t->string('name');
            $t->string('level', 10); // paud | sd | smp | sma
            $t->boolean('is_active')->default(true);
            $t->unsignedSmallInteger('sort')->default(0);

            // Identitas
            $t->string('tagline')->nullable();
            $t->text('description')->nullable();
            $t->string('logo')->nullable();
            $t->string('primary_color', 7)->default('#0f766e');
            $t->string('foundation_name')->nullable();
            $t->unsignedSmallInteger('founded_year')->nullable();

            // Kepala sekolah
            $t->string('head_title', 100)->default('Kepala Sekolah');
            $t->string('head_name')->nullable();
            $t->string('head_photo')->nullable();
            $t->text('welcome_message')->nullable();

            // Profil
            $t->text('history')->nullable();
            $t->text('vision')->nullable();
            $t->text('mission')->nullable();
            $t->text('goals')->nullable();
            $t->text('curriculum')->nullable();

            // Kontak
            $t->string('address', 500)->nullable();
            $t->string('phone', 30)->nullable();
            $t->string('email')->nullable();
            $t->string('whatsapp', 20)->nullable();
            $t->text('maps_embed_url')->nullable();
            $t->string('facebook')->nullable();
            $t->string('instagram')->nullable();
            $t->string('youtube')->nullable();
            $t->string('tiktok')->nullable();

            // Statistik (manual; boleh kosong)
            $t->unsignedInteger('stat_students')->nullable();
            $t->unsignedInteger('stat_staff')->nullable();
            $t->unsignedInteger('stat_achievements')->nullable();

            // PPDB
            $t->boolean('ppdb_open')->default(false);
            $t->unsignedSmallInteger('ppdb_year')->nullable();
            $t->date('ppdb_start')->nullable();
            $t->date('ppdb_end')->nullable();
            $t->text('ppdb_info')->nullable();

            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};