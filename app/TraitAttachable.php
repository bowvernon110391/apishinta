<?php
namespace App;

trait TraitAttachable {
    // hold all lampiran
    public function lampiran() {
        return $this->morphMany('App\Lampiran', 'attachable');
    }
}