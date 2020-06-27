<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DetailBarang extends Model
{
    // settings
    protected $table = 'detail_barang';

    protected $guarded = [
        'created_at',
        'updated_at'
    ];

    protected $attributes = [
        'uraian' => '',
        'jumlah_kemasan' => 0,
        'jenis_kemasan' => 'PK',
        'fob' => 0.0,
        'insurance' => 0.0,
        'freight' => 0.0,
        'brutto' => 0.0
    ];

    // RELATIONSHIP
    public function header() {
        return $this->morphTo();
    }

    public function kurs() {
        return $this->belongsTo(Kurs::class, 'kurs_id');
    }

    public function hs() {
        return $this->belongsTo(HsCode::class, 'hs_id');
    }

    public function jenisKemasan() {
        return $this->belongsTo(Kemasan::class, 'jenis_kemasan', 'kode');
    }

    public function jenisSatuan() {
        return $this->belongsTo(Satuan::class, 'jenis_satuan', 'kode');
    }
}
