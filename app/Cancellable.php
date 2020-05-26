<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cancellable extends Model
{
    // use table
    protected $table = 'cancellable';

    public function header() {
        return $this->belongsTo(Pembatalan::class, 'pembatalan_id', 'id');
    }

    // Computed attributes

    // return instance
    public function getInstanceAttribute() {
        return $this->cancellable_type::withTrashed()->find($this->cancellable_id);
    }

    // return uri
    public function getUriAttribute() {
        $instance = $this->instance;

        if ($instance) {
            return $instance->uri;
        }

        return null;
    }
}
