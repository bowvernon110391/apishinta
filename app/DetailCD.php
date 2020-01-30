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

    // tarif digunakan kalau dioverride aja
    public function beaMasuk($tarif = null) {
        if (isset($tarif)) {
            return ceil($this->nilai_pabean * $tarif * 0.01 / 1000.0) * 1000.0;
        } else if ($this->hs) {
            // apakah advalorum?
            if ($this->hs->jenis_tarif == 'ADVALORUM') {
                // hitung pakai tarif advalorum
                return ceil($this->nilai_pabean * (float) $this->hs->bm_tarif * 0.01 / 1000.0) * 1000.0;
            } else {
                // hitung pakai tarif spesifik
                // jumlah satuan spesifik * tarif
                return ceil($this->jumlah_satuan * $this->hs->bm_tarif / 1000.0) * 1000.0;
            }
        }
        // return ceil($this->nilai_pabean * $tarif * 0.01 / 1000.0) * 1000.0;
        return 0;
    }

    public function ppn($bm, $tarif = 10) {
        if (!isset($tarif)) {
            // echo "Using hs ppn...\n";
            $tarif = $this->hs->ppn_tarif;
        }
        return ceil( ($this->nilai_pabean + $bm) * $tarif * 0.01 / 1000.0) * 1000.0;
    }

    public function pph($bm, $tarif = 7.5) {
        return ceil( ($this->nilai_pabean + $bm) * $tarif * 0.01 / 1000.0) * 1000.0;
    }

    public function ppnbm($bm) {
        $tarif = $this->ppnbm_tarif;

        return ceil( ($this->nilai_pabean + $bm) * $tarif * 0.01 / 1000.0) * 1000.0;
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
        return (float) ( $this->kurs->kurs_idr * ($this->cif) );
    }

    // ambil kategori (tag)
    public function getKategoriTagsAttribute() {
        $tags = [];

        foreach ($this->kategoris as $k) {
            $tags[] = $k->nama;
        }

        return $tags;
    }

    // long desc gabungan antara uraian + jumlah/jenis kms + satuan, brutto + any other specs
    public function getLongDescriptionAttribute() {
        // simple desc at first
        $longDesc = "{$this->uraian} -- {$this->jumlah_kemasan} {$this->jenis_kemasan}, {$this->jumlah_satuan} {$this->jenis_satuan} -- brutto {$this->brutto} KG, netto {$this->netto} KG";
        // grab all detail sekunders in a long string?
        $detailSekunders = $this->detailSekunders->map(function ($e) {
            return "- {$e->referensiJenisDetailSekunder->nama} : {$e->data}";
        });

        $longDesc .= "\n" . implode("; ", $detailSekunders->toArray());

        return $longDesc;
    }
}
