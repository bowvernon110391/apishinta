<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PRMData extends Model
{
    // table settings
    protected $table = 'prm_data';
    protected $guarded = [
        'created_at',
        'updated_at'
    ];

    // some relations
    public function petugas() {
        return $this->belongsTo(SSOUserCache::class, 'petugas_id', 'user_id');
    }

    public function cd() {
        return $this->belongsTo(CD::class, 'cd_header_id');
    }

}
