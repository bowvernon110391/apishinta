<?php

namespace App;

/**
 * 
 */
trait TraitCancellable
{
    public function pembatalan() {
        return $this->morphToMany(Pembatalan::class, 'cancellable', 'cancellable')->withTimestamps();
    }
    /* public function logs(){
        return $this->morphMany('App\AppLog', 'loggable');
    }

    public function appendLog($message, $type="INFO", $object=null) {
        $l = new AppLog();

        $l->message = $message;
        $l->object = $object;
        $l->tipe = $type;

        // save it
        $this->logs()->save($l);

        return $l;
    } */
}
