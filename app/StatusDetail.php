<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StatusDetail extends Model
{
    // setup table
    protected $table = 'status_detail';

    // guarded
    protected $guarded = [
        'created_at',
        'updated_at'
    ];

    // ================================================
    // RELATIONS
    // ================================================
    public function status() {
        return $this->belongsTo('App\Status', 'status_id', 'id');
    }

    public function linkable() {
        return $this->morphTo('linkable');
    }
}
