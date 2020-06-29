<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Penetapan extends Model
{
    // settings
    protected $table = 'penetapan';

    protected $guarded = [
        'created_at',
        'updated_at'
    ];

    // relations
    public function pengajuan() {
        // return $this->belongsTo(DetailBarang::class, 'detail_barang_id');
        return $this->morphTo();
    }

    public function penetapan() {
        return $this->morphTo();
    }

    public function pejabat() {
        return $this->belongsTo(SSOUserCache::class, 'pejabat_id');
    }
}
