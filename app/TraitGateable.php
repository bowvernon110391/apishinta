<?php

namespace App;

trait TraitGateable
{
    // get a relation to sppb
    public function sppb() {
        return $this->morphOne(SPPB::class, 'gateable');
    }
}
