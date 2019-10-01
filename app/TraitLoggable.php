<?php

namespace App;

/**
 * 
 */
trait TraitLoggable
{
    public function logs(){
        return $this->morphMany('App\AppLog', 'loggable');
    }
}
