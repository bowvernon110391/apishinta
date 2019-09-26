<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DetailSPMB extends Model
{
    //
    protected $table = 'spmb_detail';

    public function header(){
        return $this->belongsTo('App\SPMB', 'spmb_header_id');
    }

    public function detailCD(){
        return $this->belongsTo('App\DetailCD','cd_detail_id');
    }
}
