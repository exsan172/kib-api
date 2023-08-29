<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetodePenyusutan extends Model
{
    use HasFactory;
    protected $table = 'metode_penyusutan';
    protected $fillable = [
        'uuid',
        'nama_penyusutan',
    ];
}
