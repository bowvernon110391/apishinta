<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetailSSPCP extends Model
{
    //
    use SoftDeletes;
    
    protected $table = 'sspcp_detail';

    public function details(){
        return $this->belongsTo('App\SSPCP', 'sspcp_header_id');
    }

    public function detailCD(){
        return $this->belongsTo('App\DetailCD','cd_detail_id');
    }
}
