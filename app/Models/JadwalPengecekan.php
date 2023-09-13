<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalPengecekan extends Model
{
    use HasFactory;
    protected $table = ['jadwal_pengecekan'];
    protected $fillable = [
        'uuid',
        'judul',
        'keterangan',
        'tanggal',
        'barang_id',
        'user_created',
    ];

    protected $appends = ['nama_barang', 'user_created_name'];

    // /**
    //  * Get the barang that owns the PemindahanBarang
    //  *
    //  * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    //  */
    // public function barang()
    // {
    //     return $this->belongsTo(Barang::class, 'barang_id');
    // }

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
        $barang = Barang::find($this->barang_id);
        if ($barang) {
            return $barang?->nama_barang ?? '-';
        }
        return '-';
    }

    public function getUserCreatedNameAttribute()
    {
        return $this->user?->name ?? '-';
    }
}
