<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Gudang extends Model
{
    use TraitLoggable;
    // settings
    protected $table='gudang';

    protected $guarded= [
        'created_at',
        'updated_at'
    ];

    // relation
    public function tps() {
        return $this->belongsTo(TPS::class, 'tps_id');
    }
}
