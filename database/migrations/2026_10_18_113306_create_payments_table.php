<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Murid dan program diperoleh melalui pendaftaran (enrollment_id) sehingga tidak disimpan ulang.
     * Transaksi tidak pernah dihapus; kesalahan pencatatan diselesaikan dengan menolak transaksi.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained()->restrictOnDelete();
            $table->string('code', 30)->unique();
            $table->decimal('amount', 12, 2);
            $table->string('method', 20);
            $table->date('paid_on');
            $table->string('proof_path')->nullable();
            $table->string('proof_original_name')->nullable();
            $table->string('status', 30)->default('waiting_verification');
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['enrollment_id', 'status']);
            $table->index(['status', 'paid_on']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
