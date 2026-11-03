<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('type', 20)->default('berita'); // berita | pengumuman
            $t->string('title');
            $t->string('slug');
            $t->string('excerpt', 300)->nullable();
            $t->text('body');
            $t->string('image')->nullable();
            $t->string('status', 15)->default('draft'); // draft | pending | published
            $t->timestamp('published_at')->nullable();
            $t->timestamps();

            $t->unique(['school_id', 'slug']);
            $t->index(['school_id', 'status', 'published_at']);
        });

        Schema::create('galleries', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->string('title');
            $t->string('category', 20)->default('acara'); // pembelajaran | acara | prestasi
            $t->string('image');
            $t->string('caption')->nullable();
            $t->timestamps();

            $t->index(['school_id', 'category']);
        });

        Schema::create('staff', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('position');
            $t->string('subject')->nullable();
            $t->string('staff_group', 15)->default('pendidik'); // pendidik | kependidikan
            $t->string('photo')->nullable();
            $t->unsignedSmallInteger('sort')->default(0);
            $t->timestamps();
        });

        Schema::create('programs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->string('kind', 20)->default('unggulan'); // unggulan | ekstrakurikuler
            $t->string('title');
            $t->text('description')->nullable();
            $t->string('image')->nullable();
            $t->unsignedSmallInteger('sort')->default(0);
            $t->timestamps();
        });

        Schema::create('advantages', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->string('icon', 10)->nullable();
            $t->string('title');
            $t->text('description')->nullable();
            $t->unsignedSmallInteger('sort')->default(0);
            $t->timestamps();
        });

        Schema::create('faqs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->string('question');
            $t->text('answer');
            $t->unsignedSmallInteger('sort')->default(0);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['faqs', 'advantages', 'programs', 'staff', 'galleries', 'posts'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};