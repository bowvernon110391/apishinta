<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SSPCP extends Model
{
    //
    protected $table = 'sspcp_header';

    public function details(){
        return $this->hasMany('App\DetailSSPCP', 'sspcp_header_id');
    }

    public function cd(){
        return $this->belongsTo('App\CD','cd_header_id');
    }
    
    public function lokasi(){
        return $this->belongsTo('App\Lokasi', 'lokasi_id');
    }
}
