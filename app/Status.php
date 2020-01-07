<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    // table name
    protected $table = 'status';


    protected $fillable = ['status', 'lokasi'];

    protected $attributes = [
        'statusable_id' => 0,
        'statusable_type' => ''
    ];

    public function statusable(){
        return $this->morphTo();
    }
}
