<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Total tagihan disimpan per pendaftaran (snapshot harga program saat mendaftar) agar
     * perubahan harga program di kemudian hari tidak mengubah tagihan yang sudah berjalan.
     * Pendaftaran yang sudah ada diisi dari harga programnya saat ini.
     */
    public function up(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->decimal('bill_amount', 12, 2)->default(0)->after('classroom_id');
        });

        DB::table('enrollments')->update([
            'bill_amount' => DB::raw('(select price from programs where programs.id = enrollments.program_id)'),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropColumn('bill_amount');
        });
    }
};
