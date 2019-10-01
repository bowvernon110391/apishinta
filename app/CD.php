<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CD extends Model implements IDokumen
{
    use TraitLoggable;
    use TraitDokumen;
    // enable soft Deletion
    use SoftDeletes;


    // table name
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
    
    

    // extrak data declareflags dalam bentuk flat array
    public function getFlatDeclareFlagsAttribute() {
        $flags = [];

        foreach ($this->declareFlags as $df) {
            $flags[] = $df->nama;
        }

        return $flags;
    }

    public function getJenisDokumenAttribute(){
        return 'cd';
    }

    public function getSkemaPenomoranAttribute(){
        return 'CD/'. $this->lokasi->nama . '/SH';
    }


}
