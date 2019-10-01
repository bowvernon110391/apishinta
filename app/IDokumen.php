<?php

namespace App;

interface IDokumen
{
    public function getNomorLengkapAttribute();
    public function getJenisDokumenAttribute();
    public function getSkemaPenomoranAttribute();
    public function getLastStatusAttribute();
    public function status();
    public function lock();
    public function unlock();
    public function getIsLockedAttribute();
    public function setNomorDokumen($force = false);
    public function getTahunDokAttribute();
    public function appendStatus($name);
}