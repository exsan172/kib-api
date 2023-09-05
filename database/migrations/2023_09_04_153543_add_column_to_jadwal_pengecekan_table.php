<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('jadwal_pengecekan', function (Blueprint $table) {
            $table->foreignId('barang_id')->after('tanggal');
            $table->foreignUuid('user_created')->after('barang_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jadwal_pengecekan', function (Blueprint $table) {
            $table->dropColumn('barang_id');
            $table->dropColumn('user_created');
        });
    }
};
