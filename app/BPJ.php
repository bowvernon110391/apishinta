<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BPJ extends Model
{
    use TraitLoggable;
    use TraitDokumen;
    use SoftDeletes;

    // table name
    protected $table = 'bpj';

    // default?
    protected $attributes = [
        'nomor' => 0,
        'npwp'  => '-',
        'alamat'=> '',
        'penjamin'=> '',
        'statusable_id' => 0,
        'statusable_type' => ''
    ];

    // fillable
    protected $guarded = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    // relations
    //==========================================
    public function guaranteeable() {
        return $this->morphTo();
    }

    public function lokasi() {
        return $this->belongsTo('App\Lokasi', 'lokasi_id');
    }

    public function penumpang() {
        return $this->belongsTo('App\Penumpang', 'penumpang_id');
    }

    public function getJenisDokumenAttribute(){
        return 'bpj';
    }

    public function getSkemaPenomoranAttribute(){
        return 'BPJ/' . $this->lokasi->nama . '/SH';
    }
}
