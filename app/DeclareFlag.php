<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DeclareFlag extends Model
{
    //
    protected $table = 'declare_flag';
    
    public $timestamps = false;

    /**
     * RELATIONS
     */
    public function cds(){
        return $this->belongsToMany('App\CD', 'cd_header_declare_flag', 'declare_flag_id', 'cd_header_id');
    }

    /**
     * SCOPES
     */
    public function scopeByName($query, $names) {
        // convert to array if it's still in string
        $nameArr = is_array($names) ? names : array_map(function($e) {
            return trim($e);
        }, explode(",", $names));

        return $query->whereIn('nama', $nameArr);
    }
}
