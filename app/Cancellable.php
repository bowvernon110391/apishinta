<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cancellable extends Model
{
    // use table
    protected $table = 'cancellable';

    public function header() {
        return $this->belongsTo(Pembatalan::class, 'pembatalan_id', 'id');
    }
}
