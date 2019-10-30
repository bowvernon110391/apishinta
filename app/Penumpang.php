<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Penumpang extends Model implements ILinkable
{
    //
    protected $table = 'penumpang';
    protected $with = ['negara'];

    public function getUriAttribute() {
        return '/penumpang/'.$this->id;
    }

    public function getLinksAttribute() {
        $links = [
            [
                'rel'   => 'self',
                'uri'   => $this->uri
            ]
        ];

        return $links;
    }

    public function negara() {
        return $this->belongsTo('App\Negara', 'kebangsaan', 'kode');
    }

    // SCOPES
    public function scopeByNegara($query, $negara) {
        return $query->where('kebangsaan', 'like', "%{$negara}%")
                    ->orWhereHas('negara', function ($qNegara) use ($negara) {
                        return $qNegara->where('uraian', 'like', "%{$negara}%");
                    });
    }
}
