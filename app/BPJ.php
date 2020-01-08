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
        'no_dok' => 0,
        'no_identitas'  => '-',
        'alamat'=> '',
        'penjamin'=> '',
        'guaranteeable_id' => 0,
        'guaranteeable_type' => ''
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

    // scopes
    //============================================
    public function scopeFrom($query, $from) {
        return $query->where('tanggal', '>=', $from);
    }

    public function scopeTo($query, $to) {
        return $query->where('tanggal', '<=', $to);
    }

    public function scopeByTanggal($query, $from, $to) {
        return $query->from($from)
                    ->to($to);
    }

    public function scopeByPenjamin($query, $penjamin) {
        return $query->where('penjamin', 'like', "%{$penjamin}%");
    }

    public function scopeByPenumpang($query, $penumpang) {
        return $query->whereHas('penumpang', function($q) use ($penumpang) {
            $q->where('nama', 'like', "%{$penumpang}%");
        });
    }

    public function scopeByNoIdentitas($query, $noIdentitas) {
        return $query->where('no_identitas', 'like', "%{$noIdentitas}%");
    }

    public function scopeByQuery($query, $q='', $from, $to) {
        return $query->byPenjamin($q)
                ->orWhere(function ($query) use ($q) {
                    $query->byPenumpang($q);
                })
                ->when($from, function ($query) use ($from) {
                    $query->from($from);
                })
                ->when($to, function ($query) use ($to) {
                    $query->to($to);
                })
                ->latest()
                ->orderBy('tgl_dok', 'desc');
    }
}
