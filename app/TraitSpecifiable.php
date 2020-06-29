<?php

namespace App;

trait TraitSpecifiable {
    // RELATIONSHIP
    public function penetapan() {
        $class = get_class($this);
        return $this->hasOneThrough($class, Penetapan::class, 'pengajuan_id', 'id', 'id', 'penetapan_id')
                    ->where('pengajuan_type', $class);
    }
    public function pengajuan() {
        $class = get_class($this);
        return $this->hasOneThrough($class, Penetapan::class, 'penetapan_id', 'id', 'id', 'pengajuan_id')
                    ->where('penetapan_type', $class);
    }

    public function pivotPengajuan() {
        return $this->morphOne(Penetapan::class, 'pengajuan');
    }
    public function pivotPenetapan() {
        return $this->morphOne(Penetapan::class, 'penetapan');
    }

    // Attributes
    public function getIsPenetapanAttribute() {
        return $this->pivotPenetapan != null;
    }

    // SCOPES
    public function scopeIsPengajuan($query) {
        return $query->whereDoesntHave('pivotPenetapan');
    }
    public function scopeIsPenetapan($query) {
        return $query->whereHas('pivotPenetapan');
    }
}