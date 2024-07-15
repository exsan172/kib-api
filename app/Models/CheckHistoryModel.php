<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckHistoryModel extends Model
{
    use HasFactory;
    protected $table = 'check_history';
    protected $fillable = ['lokasi_id', 'barang_id', 'kondisi'];

    public function lokasi()
    {
        return $this->belongsTo(Lokasi::class, "lokasi_id");
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class, "barang_id");
    }
}
