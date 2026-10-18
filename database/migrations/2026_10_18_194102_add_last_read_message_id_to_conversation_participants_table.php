<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menyimpan ID pesan terakhir yang sudah dibaca peserta, agar status belum dibaca tidak
     * bergantung pada stempel waktu berpresisi detik.
     */
    public function up(): void
    {
        Schema::table('conversation_participants', function (Blueprint $table) {
            $table->unsignedBigInteger('last_read_message_id')->nullable()->after('last_read_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conversation_participants', function (Blueprint $table) {
            $table->dropColumn('last_read_message_id');
        });
    }
};
