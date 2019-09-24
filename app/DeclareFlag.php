<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DeclareFlag extends Model
{
    //
    protected $table = 'declare_flag';

    public function cds(){
        return $this->belongsToMany('App\CD', 'cd_header_declare_flag', 'declare_flag_id', 'cd_header_id');
    }
}
