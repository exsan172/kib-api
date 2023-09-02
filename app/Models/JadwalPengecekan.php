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
    ];
}
