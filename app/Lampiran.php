<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

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
}
