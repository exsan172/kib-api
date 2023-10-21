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
        Schema::create('barang', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->string('nama_barang');
            $table->string('kode_barang', 50);
            $table->string('kode_barang_resmi', 20)->nullable();
            $table->string('kondisi')->default('baik');
            $table->string('nilai_perolehan')->nullable();
            $table->string('tahun_pembelian')->nullable();
            $table->string('masa_manfaat')->nullable();
            $table->text('keterangan')->nullable();
            $table->char('status', 1)->default(1);
            $table->boolean('penyusutan_barang')->nullable();
            $table->foreignId('kategori_barang_id');
            $table->foreignId('lokasi_id');
            $table->foreignId('metode_penyusutan_id');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barang');
    }
};
