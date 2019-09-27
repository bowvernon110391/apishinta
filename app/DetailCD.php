<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DetailCD extends Model
{
    //
    protected $table = 'cd_detail';

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

    //=================================================================================================
    // COMPUTED PROPERTIES GO HERE!!
    //=================================================================================================

    // hitung cif
    public function getCifAttribute() {
        return (float) $this->fob + (float) $this->insurance + (float) $this->freight;
    }
    
    // hitung nilai pabean?
    public function getNilaiPabeanAttribute() {
        return (float) $this->nilai_valuta * $this->cif;
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
