<?php

namespace App;

interface IGateable
{
    // must provide us with data sppb
    public function getDataSppbAttribute();

    // get a relation to sppb
    public function sppb();
}
