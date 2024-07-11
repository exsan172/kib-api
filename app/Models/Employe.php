<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employe extends Model
{
    use HasFactory;
    protected $table = 'employes';
    protected $fillable = ['foto', 'nama', 'nip', 'instansi', 'jabatan'];

    public function barangs()
    {
        return $this->hasMany(Barang::class, 'karyawan_id');
    }
}
