<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class IS extends Model
{
    protected $table = 'is_header';

    public function details(){
        return $this->hasMany('App\DetailIS', 'is_header_id');
    }

    public function cd(){
        return $this->belongsTo('App\CD','cd_header_id');
    }
    
    public function lokasi(){
        return $this->belongsTo('App\Lokasi', 'lokasi_id');
    }
}
