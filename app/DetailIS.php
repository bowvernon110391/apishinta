<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DetailIS extends Model
{
    //
    protected $table = 'is_detail';

    public function details(){
        return $this->belongsTo('App\IS', 'is_header_id');
    }

    public function detailCD(){
        return $this->belongsTo('App\DetailCD','cd_detail_id');
    }
}
