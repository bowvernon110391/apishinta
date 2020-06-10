<?php

namespace App;

/**
 * 
 */
trait TraitInspectable
{
    public function lhp() {
        return $this->morphMany(LHP::class, 'inspectable');
    }
}
