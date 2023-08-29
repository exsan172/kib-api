<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogHistoryBarang extends Model
{
    use HasFactory;
    protected $table = 'log_history_barang';
    protected $fillable = [
        'user_updated',
        'barang_id',
        'keterangan',
    ];
    protected $appends = ['user_updated_name'];

    /**
     * Get the user that owns the LogHistoryBarang
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function userUpdated()
    {
        return $this->belongsTo(User::class, 'user_updated');
    }

    function getUserUpdatedNameAttribute()
    {
        return $this->userUpdated?->name ?? '-';
    }
}
