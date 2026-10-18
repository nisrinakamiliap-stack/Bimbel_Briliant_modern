<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Honor guru tidak menggunakan tabel payments karena payments terikat ke enrollment murid.
     * Satu baris honorarium mewakili satu periode pembayaran (bulan + tahun) untuk satu guru.
     * Jumlah sesi dan tarif per sesi dicatat eksplisit agar riwayat tidak berubah jika jadwal diedit.
     */
    public function up(): void
    {
        Schema::create('teacher_honorariums', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->restrictOnDelete();
            $table->unsignedTinyInteger('period_month');
            $table->unsignedSmallInteger('period_year');
            $table->unsignedSmallInteger('session_count')->default(0);
            $table->decimal('rate_per_session', 12, 2)->default(0);
            $table->decimal('base_amount', 12, 2)->default(0);
            $table->decimal('adjustments', 12, 2)->default(0);
            $table->decimal('deductions', 12, 2)->default(0);
            $table->decimal('amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->string('status', 20)->default('draft');
            $table->date('paid_on')->nullable();
            $table->string('payment_method', 20)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('paid_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->unique(['teacher_id', 'period_year', 'period_month']);
            $table->index(['status', 'period_year', 'period_month']);
            $table->index(['teacher_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_honorariums');
    }
};
