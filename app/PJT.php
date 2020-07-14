<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PJT extends Model
{
    use TraitLoggable;
    // settings
    protected $table = 'pjt';

    protected $guarded = [
        'created_at',
        'updated_at'
    ];
}
