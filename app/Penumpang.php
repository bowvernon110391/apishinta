<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Penumpang extends Model implements ILinkable
{
    // trait
    use TraitLoggable;

    protected $table = 'penumpang';
    // protected $with = ['negara'];

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

        $cdLinks = $this->cds
                    ->map(function($e) { 
                        // grab the links attribute
                        return $e->links; 
                    })
                    ->map(function($e) {
                        // pick the first one only
                        $d = $e[0];
                        $d['rel'] = 'cd';

                        return $d;
                    });
        // print_r($cdLinks);

        // return $links;
        return array_merge($links, $cdLinks->toArray());
        // return array_push($links, array_values($cdLinks));
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

    // All document related to this piece of shit
    public function cds() {
        return $this->hasMany('App\CD', 'penumpang_id', 'id');
    }
}
