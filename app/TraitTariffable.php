<?php
namespace App;

trait TraitTariffable {
    public function tarif()
    {
        return $this->morphMany(Tarif::class, 'tariffable');
    }
}