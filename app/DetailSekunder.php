<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DetailSekunder extends Model
{
    //
    protected $table = 'detail_sekunder';

    public $timestamps = false;

    public function refrensiJenisDetailSekunder(){
        return $this->belongsTo('App\ReferensiJenisDetailSekunder', 'jenis_detail_sekunder_id');
    }

}
