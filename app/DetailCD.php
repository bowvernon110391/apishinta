<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetailCD extends Model
{
    //
    use SoftDeletes;
    
    protected $table = 'cd_detail';
    protected $guarded = [
        'created_at',
        'deleted_at',
        'updated_at'
    ];

    public function header(){
        return $this->belongsTo('App\CD', 'cd_header_id');
    }

    public function kategoris(){
        return $this->belongsToMany('App\Kategori', 'cd_detail_kategori', 'cd_detail_id', 'kategori_id');
    }

    public function detailSekunders(){
        return $this->hasMany('App\DetailSekunder', 'cd_detail_id');
    }

    public function detailSSPCP(){
        return $this->hasOne('App\DetailSSPCP','cd_detail_id');
    }

    public function kurs() {
        return $this->belongsTo('App\Kurs', 'kurs_id');
    }

    public function hs() {
        return $this->belongsTo('App\HsCode', 'hs_code', 'kode');
    }

    public function kemasan() {
        return $this->belongsTo('App\Kemasan', 'jenis_kemasan', 'kode');
    }

    public function satuan() {
        return $this->belongsTo('App\Satuan', 'jenis_satuan', 'kode');
    }

    //=================================================================================================
    // COMPUTED PROPERTIES GO HERE!!
    //=================================================================================================

    // hitung cif
    public function getCifAttribute() {
        return (float) $this->fob + (float) $this->insurance + (float) $this->freight;
    }
    
    // hitung nilai pabean?
    public function getNilaiPabeanAttribute() {
        return (float) $this->kurs->kurs_idr * $this->cif;
    }

    // ambil kategori (tag)
    public function getKategoriTagsAttribute() {
        $tags = [];

        foreach ($this->kategoris as $k) {
            $tags[] = $k->nama;
        }

        return $tags;
    }
}
