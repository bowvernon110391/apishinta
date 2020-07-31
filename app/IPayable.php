<?php

namespace App;

interface IPayable
{
    // relations
    public function bppm(); // a one to one, preferably

    // also has billing? preferably several
    public function billing(); // a one to many, cause probably the billing are composite
    // must have jenis penerimaan
    public function getJenisPenerimaanAttribute();
    // grab info pembayar
    public function getPayerAttribute();

    // some scopes?
    public function scopeNotBilled($query);
    public function scopeBilled($query);
}
