<?php

namespace App;

interface ILinkable {
    public function getLinksAttribute();
    public function getUriAttribute();
}