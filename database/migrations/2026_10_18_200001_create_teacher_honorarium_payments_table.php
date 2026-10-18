<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Transaksi pembayaran honor guru, mendukung pembayaran bertahap (sebagian)
     * maupun pembayaran penuh dengan nomor referensi dan berkas bukti pembayaran.
     */
    public function up(): void
    {
        Schema::create('teacher_honorarium_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_honorarium_id')
                ->constrained('teacher_honorariums')
                ->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('payment_method', 20);
            $table->date('paid_on');
            $table->string('reference_number', 100)->nullable();
            $table->string('proof_path')->nullable();
            $table->string('proof_original_name')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['teacher_honorarium_id', 'paid_on']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_honorarium_payments');
    }
};
