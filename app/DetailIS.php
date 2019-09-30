<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetailIS extends Model
{
    //
    use SoftDeletes;
    
    protected $table = 'is_detail';

    public function header(){
        return $this->belongsTo('App\IS', 'is_header_id');
    }

    public function detailCD(){
        return $this->belongsTo('App\DetailCD','cd_detail_id');
    }
}
