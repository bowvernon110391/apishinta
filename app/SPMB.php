<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SPMB extends Model implements IDokumen, IInspectable
{
    use TraitLoggable;
    use TraitDokumen;
    use TraitInspectable;

    use SoftDeletes;
    
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
