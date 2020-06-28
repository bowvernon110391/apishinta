<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DetailBarang extends Model implements ISpecifiable
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

    // penetapan is in the same table, refer to ourselves
    public function penetapan()
    {
        return $this->hasOne(Penetapan::class, 'detail_barang_id');
    }

    // detail barang refer to self (if we're penetapan)
    public function detailBarang() {
        return $this->hasOneThrough(DetailBarang::class, Penetapan::class, 'penetapan_id', 'id', 'id', 'detail_barang_id');
    }

    public function detailPenetapan() {
        return $this->hasOneThrough(DetailBarang::class, Penetapan::class, 'detail_barang_id', 'id', 'id', 'penetapan_id');
    }

    public function tarif()
    {
        return $this->hasMany(Tarif::class, 'tariffable_id');
    }

    // ATTRIBUTES!!!
    public function getIsPenetapanAttribute() {
        return $this->detailBarang != null;
    }
}
