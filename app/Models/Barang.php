<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Barang extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'barang';
    protected $fillable = [
        'uuid',
        'nama_barang',
        'kode_barang',
        'tahun_perolehan',
        'kondisi',
        'nilai_perolehan',
        'nilai_pertahun',
        'tahun_pembelian',
        'masa_manfaat',
        'keterangan',
        'status',
        'penyusutan_barang',
        'kategori_barang_id',
        'lokasi_id',
        'metode_penyusutan_id',
        'user_created'
    ];

    protected $appends = ['nama_kategori', 'nama_penyusutan', 'user_created_name'];
    protected $with = ['fotoBarang', 'riwayatPengecekan', 'lokasi'];

    /**
     * Get the kategori that owns the Barang
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function kategori()
    {
        return $this->belongsTo(KategoriBarang::class, 'kategori_barang_id');
    }

    /**
     * Get the metodePenyusutan that owns the Barang
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function metodePenyusutan()
    {
        return $this->belongsTo(MetodePenyusutan::class, 'metode_penyusutan_id');
    }

    /**
     * Get the userCreated that owns the Barang
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function userCreated()
    {
        return $this->belongsTo(User::class, 'user_created');
    }

    /**
     * Get all of the logHistory for the Barang
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function logHistory()
    {
        return $this->hasMany(LogHistoryBarang::class, 'barang_id');
    }

    /**
     * Get all of the logHistory for the Barang
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function lokasi()
    {
        return $this->belongsTo(Lokasi::class);
    }

    /**
     * Get all of the jadwals for the Barang
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function jadwals()
    {
        return $this->hasMany(JadwalPengecekan::class, 'barang_id');
    }

    /**
     * Get all of the jadwals for the Barang
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function riwayatPengecekan()
    {
        return $this->hasMany(JadwalPengecekan::class, 'barang_id');
    }

    /**
     * Get all of the fotoBarang for the Barang
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function fotoBarang()
    {
        return $this->hasMany(FotoBarang::class, 'barang_id');
    }

    public function getNamaKategoriAttribute()
    {
        return $this->kategori?->nama_kategori ?? '-';
    }

    // public function getRiwayatPengecekanAttribute()
    // {
    //     return $this->jadwals()->get();
    // }

    public function getNamaPenyusutanAttribute()
    {
        return $this->metodePenyusutan?->nama_penyusutan ?? '-';
    }

    public function getUserCreatedNameAttribute()
    {
        return $this->userCreated?->name ?? '-';
    }
}
