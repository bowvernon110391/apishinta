<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetailSPMB extends Model
{
    //
    use SoftDeletes;
    
    protected $table = 'spmb_detail';

    public function header(){
        return $this->belongsTo('App\SPMB', 'spmb_header_id');
    }

    public function detailCD(){
        return $this->belongsTo('App\DetailCD','cd_detail_id');
    }
}
