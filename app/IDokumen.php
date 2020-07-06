<?php

namespace App;

interface IDokumen
{
    public function getNomorLengkapAttribute();
    public function getJenisDokumenAttribute();
    public function getJenisDokumenLengkapAttribute();
    public function getSkemaPenomoranAttribute();

    public function lockAndSetNumber(); // lock status, and set number

    public function setNomorDokumen($force = false);
    public function getTahunDokAttribute();
}