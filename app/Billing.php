<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Billing extends Model
{
    use TraitLoggable;

    // table name
    protected $table = 'billing';

    protected $attributes = [
        'billable_id'   => 0,
        'billable_type' => ''
    ];

    protected $guarded = [
        'created_at',
        'updated_at'
    ];

    // relations
    public function billable() {
        return $this->morphTo();
    }
}
