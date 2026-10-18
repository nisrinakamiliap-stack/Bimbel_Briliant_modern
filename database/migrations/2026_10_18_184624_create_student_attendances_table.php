<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Satu sesi belajar = satu jadwal mingguan pada satu tanggal. Constraint unik mencegah
     * murid memiliki lebih dari satu catatan absensi untuk sesi yang sama. Catatan tidak
     * pernah dihapus; perubahan dilakukan melalui koreksi yang menyimpan status sebelumnya.
     */
    public function up(): void
    {
        Schema::create('student_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained()->restrictOnDelete();
            $table->date('session_date');
            $table->foreignId('student_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('enrollment_id')->constrained()->restrictOnDelete();
            $table->string('status', 20);
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('previous_status', 20)->nullable();
            $table->foreignId('corrected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('corrected_at')->nullable();
            $table->text('correction_reason')->nullable();
            $table->timestamps();

            $table->unique(['schedule_id', 'session_date', 'student_id']);
            $table->index(['student_id', 'session_date']);
            $table->index(['session_date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_attendances');
    }
};
