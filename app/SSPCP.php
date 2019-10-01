<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SSPCP extends Model
{
    //
    use SoftDeletes;

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
