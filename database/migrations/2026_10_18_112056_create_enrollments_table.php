<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Kelas boleh kosong karena murid dapat mendaftar ke program terlebih dahulu dan
     * ditempatkan ke kelas kemudian. Pendaftaran tidak pernah dihapus; pembatalan
     * dilakukan melalui status agar riwayat tetap utuh.
     */
    public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('program_id')->constrained()->restrictOnDelete();
            $table->foreignId('classroom_id')->nullable()->constrained('classes')->restrictOnDelete();
            $table->date('enrolled_on');
            $table->string('status', 20)->default('pending');
            $table->timestamp('status_changed_at')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'program_id', 'status']);
            $table->index(['classroom_id', 'status']);
            $table->index(['status', 'enrolled_on']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
