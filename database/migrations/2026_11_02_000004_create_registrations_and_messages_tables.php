<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registrations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->unsignedSmallInteger('year');
            $t->unsignedInteger('seq');
            $t->string('reg_number')->unique();

            // Calon siswa
            $t->string('student_name');
            $t->string('nik', 20)->nullable();
            $t->string('gender', 1); // L | P
            $t->string('birth_place');
            $t->date('birth_date');
            $t->string('religion', 30)->nullable();
            $t->text('address');
            $t->string('previous_school')->nullable();

            // Orang tua / wali
            $t->string('parent_name');
            $t->string('parent_phone', 30);
            $t->string('parent_email')->nullable();
            $t->string('parent_job')->nullable();

            // Dokumen (disimpan di disk privat)
            $t->string('doc_birth_cert')->nullable();
            $t->string('doc_family_card')->nullable();
            $t->string('doc_photo')->nullable();

            $t->string('status', 15)->default('baru');
            $t->text('admin_note')->nullable();
            $t->timestamps();

            $t->unique(['school_id', 'year', 'seq']);
            $t->index(['school_id', 'status']);
        });

        Schema::create('contact_messages', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('email')->nullable();
            $t->string('phone', 30)->nullable();
            $t->text('message');
            $t->boolean('is_read')->default(false);
            $t->timestamps();

            $t->index(['school_id', 'is_read']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
        Schema::dropIfExists('registrations');
    }
};