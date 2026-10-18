<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Guru disimpan pada catatan (bukan hanya melalui kelas) karena guru kelas dapat berganti;
     * riwayat harus menunjukkan siapa yang benar-benar mengajar. Satu sesi jadwal hanya boleh
     * memiliki satu catatan check-in.
     */
    public function up(): void
    {
        Schema::create('teacher_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained()->restrictOnDelete();
            $table->date('session_date');
            $table->foreignId('teacher_id')->constrained('users')->restrictOnDelete();
            $table->timestamp('checked_in_at');
            $table->timestamp('checked_out_at')->nullable();
            $table->string('status', 20);
            $table->string('previous_status', 20)->nullable();
            $table->foreignId('corrected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('corrected_at')->nullable();
            $table->text('correction_reason')->nullable();
            $table->timestamps();

            $table->unique(['schedule_id', 'session_date']);
            $table->index(['teacher_id', 'session_date']);
            $table->index(['session_date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_attendances');
    }
};
