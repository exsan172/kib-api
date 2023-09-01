<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lokasi extends Model
{
    use HasFactory;
    protected $table = 'lokasi';
    protected $fillable = ['uuid', 'nama_lokasi', 'parent_id'];
    protected $with = ['children'];

    /**
     * Get the parent that owns the Menu
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(Lokasi::class, 'parent_id')->where('parent_id', null)->with('parent');
    }

    /**
     * Get all of the children for the Lokasi
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children()
    {
        return $this->hasMany(Lokasi::class, 'parent_id')->with('children')->orderBy('created_at', 'ASC');
    }

    /**
     * Get all of the barang for the Lokasi
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function barang()
    {
        return $this->hasMany(Barang::class, 'lokasi_id');
    }
}
