<?php

namespace App;

/**
 * This is for a document that MUST HAVE INSTRUCTION BEFORE 
 * INSPECTION
 */
trait TraitInstructable
{
    public function instruksiPemeriksaan(){
        return $this->morphOne('App\InstruksiPemeriksaan', 'instructable');
    }

    public function lhp()
    {
        return $this->hasOneThrough('App\LHP', 'App\InstruksiPemeriksaan', 'instructable_id', 'inspectable_id');
    }
}
