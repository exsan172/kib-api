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
        Schema::create('pemindahan_barang', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->foreignId('barang_id');
            $table->foreignUuid('user_created');
            $table->string('type')->nullable();
            // jual
            $table->string('nama_pembeli')->nullable();
            $table->string('harga_jual')->nullable();
            $table->string('tanggal_jual')->nullable();

            // tukar barang
            $table->string('nama_penukar')->nullable();
            $table->string('barang_yang_ditukar')->nullable();
            $table->string('tanggal_tukar')->nullable();

            // hibah barang
            $table->string('nama_penerima')->nullable();
            $table->string('tanggal_diterima')->nullable();

            // pemusnahan_barang
            $table->string('alasan_pemusnahan')->nullable();
            $table->string('tanggal_pemusnahan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemindahan_barang');
    }
};
