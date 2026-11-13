<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('about_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('title', 120);
            $table->string('image');
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        // Pindahkan foto tunggal lama (schools.about_photo) menjadi foto pertama di tabel baru,
        // supaya foto yang sudah diunggah tidak hilang.
        if (Schema::hasColumn('schools', 'about_photo')) {
            DB::table('schools')
                ->whereNotNull('about_photo')->where('about_photo', '!=', '')
                ->get(['id', 'name', 'about_photo'])
                ->each(function ($s) {
                    DB::table('about_photos')->insert([
                        'school_id' => $s->id,
                        'title' => 'Tentang '.$s->name,
                        'image' => $s->about_photo,
                        'sort' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('about_photos');
    }
};