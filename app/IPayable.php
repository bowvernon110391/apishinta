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
    // must have npwp
    public function getNpwpPembayarAttribute();

    // some scopes?
    public function scopeNotBilled($query);
}
