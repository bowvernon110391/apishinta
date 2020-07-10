<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dokkap extends Model
{
    // Dokkap bisa punya lampiran
    use TraitAttachable;
    use TraitLoggable;

    use SoftDeletes;

    protected $table = 'dokkap';

    protected $guarded = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    // default relations to load
    protected $with = [
        'referensiJenisDokkap'
    ];

    public function referensiJenisDokkap() {
        return $this->belongsTo('App\ReferensiJenisDokkap', 'jenis_dokkap_id');
    }

    public function master() {
        return $this->morphTo();
    }

    // scope
    public function scopeByKode($query, $kode) {
        return $query->whereHas('referensiJenisDokkap', function ($q) use ($kode) {
            $q->byCode($kode);
        });
    }
}
