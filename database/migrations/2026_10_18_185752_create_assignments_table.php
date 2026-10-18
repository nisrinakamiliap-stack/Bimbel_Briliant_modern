<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Skala nilai ditentukan per tugas (`max_score`) karena rancangan aplikasi tidak menetapkan
     * skala global. Tugas yang sudah memiliki pengumpulan tidak dapat dihapus.
     */
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classroom_id')->constrained('classes')->restrictOnDelete();
            $table->foreignId('material_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title', 200);
            $table->longText('instructions');
            $table->dateTime('due_at');
            $table->unsignedSmallInteger('max_score')->default(100);
            $table->boolean('allow_late_submission')->default(false);
            $table->boolean('allow_resubmission')->default(true);
            $table->string('attachment_path')->nullable();
            $table->string('attachment_original_name')->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['classroom_id', 'status']);
            $table->index('due_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
