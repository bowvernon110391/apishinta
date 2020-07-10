<?php

namespace App;

trait TraitPayable
{
    public function bppm() {
        return $this->morphOne(BPPM::class, 'payable');
    }

    public function billing() {
        return $this->morphMany(Billing::class, 'billable');
    }

    // scopes
    public function scopeNotBilled($query) {
        return $query->whereDoesntHave('billing');
    }
}
