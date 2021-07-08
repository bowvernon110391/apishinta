<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Fasilitas extends Model
{
    // some default shiet
    protected $table = 'fasilitas';

    // relations
    public function detailBarang() {
        return $this->belongsTo(DetailBarang::class, 'detail_barang_id');
    }

    public function jenisPungutan() {
        return $this->belongsTo(ReferensiJenisPungutan::class, 'jenis_pungutan_id');
    }

    // computed attributes
    public function getDeskripsiAttribute() {
        if ($this->jenis == 'KERINGANAN') {
            return "{$this->jenis} {$this->jenisPungutan->nama}, {$this->jenisPungutan->kode} = {$this->tarif_keringanan}%";
        }

        return "{$this->jenis} {$this->jenisPungutan->nama}";
    }
}
