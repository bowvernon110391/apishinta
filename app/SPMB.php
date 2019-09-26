<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SPMB extends Model
{
    protected $table = 'spmb_header';

    public function details(){
        return $this->hasMany('App\DetailSPMB', 'spmb_header_id');
    }

    public function cd(){
        return $this->belongsTo('App\CD','cd_header_id');
    }
    
    public function lokasi(){
        return $this->belongsTo('App\Lokasi', 'lokasi_id');
    }
}
