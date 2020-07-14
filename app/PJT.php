<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PJT extends Model
{
    // settings
    protected $table = 'pjt';

    protected $guarded = [
        'created_at',
        'updated_at'
    ];
}
