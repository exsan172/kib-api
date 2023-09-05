<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PemindahanBarang extends Model
{
    use HasFactory;
    protected $table = 'pemindahan_barang';
    protected $fillable = [
        'uuid',
        'barang_id',
        'user_created',
        'type',
        'nama_pembeli',
        'harga_jual',
        'tanggal_jual',
        'nama_penukar',
        'barang_yang_ditukar',
        'tanggal_tukar',
        'nama_penerima',
        'tanggal_diterima',
        'alasan_pemusnahan',
        'tanggal_pemusnahan',
    ];

    protected $appends = ['nama_barang', 'user_created_name'];

    /**
     * Get the barang that owns the PemindahanBarang
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }

    /**
     * Get the user that owns the PemindahanBarang
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_created');
    }

    public function getNamaBarangAttribute()
    {
        return $this->barang?->nama_barang ?? '-';
    }

    public function getUserCreatedNameAttribute()
    {
        return $this->user?->name ?? '-';
    }
}
