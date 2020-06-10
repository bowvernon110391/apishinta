<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Dokkap extends Model
{
    // Dokkap bisa punya lampiran
    use TraitAttachable;
    use TraitLoggable;

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
}
