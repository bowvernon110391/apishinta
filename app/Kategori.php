<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    //
    protected $table = 'kategori';

    // query kategori by name
    public function scopeByName($query, $name) {
        return $query->where('nama', 'like', "%$name%");
    }

    // query by exact name list
    public function scopeByNameList($query, $namelist) {
        if (!is_array($namelist)) {
            $namelist = explode(',', $namelist);
        }

        // now, query using in
        return $query->whereIn('nama', $namelist);
    }
}
