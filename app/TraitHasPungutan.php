<?php
namespace App;

trait TraitHasPungutan {
    public function pungutan() {
        return $this->morphMany(Pungutan::class, 'dutiable');
    }
}