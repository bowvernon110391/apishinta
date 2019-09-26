<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DetailSekunder extends Model
{
    //
    protected $table = 'detail_sekunder';

    public $timestamps = false;

    // default relations to load
    protected $with = [
        'referensiJenisDetailSekunder'
    ];

    public function referensiJenisDetailSekunder(){
        return $this->belongsTo('App\ReferensiJenisDetailSekunder', 'jenis_detail_sekunder_id');
    }

}
