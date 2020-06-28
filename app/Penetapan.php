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
    public function barang() {
        return $this->belongsTo(DetailBarang::class, 'detail_barang_id');
    }

    public function data() {
        return $this->belongsTo(DetailBarang::class, 'penetapan_id');
    }

    public function pejabat() {
        return $this->belongsTo(SSOUserCache::class, 'pejabat_id');
    }
}
