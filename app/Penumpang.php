<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Penumpang extends Model implements ILinkable
{
    //
    protected $table = 'penumpang';
    protected $with = [];

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
}
