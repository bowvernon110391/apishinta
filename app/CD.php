<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CD extends Model
{
    protected $table = 'cd_header';

    // default values
    protected $attributes = [
        'no_dok'    => 0,   // 0 berarti blom dinomorin
        'npwp'      => '',
        'nib'       => '',
        'alamat'    => '',
        'no_flight' => ''
    ];

    // always loaded relations
    protected $with = [
        'lokasi',
        'declareFlags'
    ];

    public function details(){
        return $this->hasMany('App\DetailCD', 'cd_header_id');
    }

    public function lokasi(){
        return $this->belongsTo('App\Lokasi', 'lokasi_id');
    }

    public function declareFlags(){
        return $this->belongsToMany('App\DeclareFlag', 'cd_header_declare_flag', 'cd_header_id', 'declare_flag_id');
    }

    public function penumpang(){
        return $this->belongsTo('App\Penumpang', 'penumpang_id');
    }

    public function sspcp(){
        return $this->hasOne('App\SSPCP','cd_header_id');
    }

    public function imporSementara(){
        return $this->hasOne('App\IS','cd_header_id');
    }

    public function spmb(){
        return $this->hasOne('App\SPMB','cd_header_id');
    }
    
    //=================================================================================================
    // COMPUTED PROPERTIES GO HERE!!
    //=================================================================================================
    // ambil data tahun dok dari tgl
    public function getTahunDokAttribute() {
        return (int)substr($this->tgl_dok, 0, 4);
    }

    // nomor lengkap, e.g. 000001/CD/T2F/SH/2019
    public function getNomorLengkapAttribute() {
        if ($this->no_dok == 0) {
            return null;
        }
        
        $nomorLengkap = str_pad($this->no_dok, 6,"0", STR_PAD_LEFT)
                        .'/CD/'
                        .$this->lokasi->nama
                        .'/SH/'
                        .$this->tahun_dok;
        return $nomorLengkap;
    }

    // extrak data declareflags dalam bentuk flat array
    public function getFlatDeclareFlagsAttribute() {
        $flags = [];

        foreach ($this->declareFlags as $df) {
            $flags[] = $df->nama;
        }

        return $flags;
    }
}
