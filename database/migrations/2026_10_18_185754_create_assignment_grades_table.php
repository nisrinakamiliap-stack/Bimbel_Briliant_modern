<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Satu pengumpulan memiliki paling banyak satu penilaian (unique). Perubahan nilai menyimpan
     * nilai sebelumnya agar dapat ditelusuri.
     */
    public function up(): void
    {
        Schema::create('assignment_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->unique()->constrained()->restrictOnDelete();
            $table->foreignId('grader_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('score', 6, 2);
            $table->text('feedback')->nullable();
            $table->timestamp('graded_at');
            $table->decimal('previous_score', 6, 2)->nullable();
            $table->unsignedSmallInteger('revision_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignment_grades');
    }
};
