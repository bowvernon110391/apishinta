<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pembatalan extends Model
{
    use TraitLoggable;
    use TraitDokumen;
    // automatically synchronized with 'pembatalans'

    // guarded
    protected $guarded = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function cd() {
        return $this->morphedByMany(CD::class, 'cancellable', 'cancellable');
    }
}
