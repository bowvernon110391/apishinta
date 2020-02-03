<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Lampiran extends Model
{
    // setup tables and default relations
    protected $table = 'lampiran';

    protected $guarded = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    // Polymorphic relations
    public function Attachable() {
        return $this->morphTo();
    }

    // COMPUTED PROPS
    public function getUrlAttribute() {
        return asset(Storage::url($this->diskfilename));
    }
}
