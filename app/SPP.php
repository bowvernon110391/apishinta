<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SPP extends Model
{
    use TraitLoggable;
    use TraitDokumen {
        lock as public traitLock;
        unlock as public traitUnlock;
    }
    // enable soft deletion
    use SoftDeletes;

    // table name
    protected $table = 'spp_header';

    // default values
    protected $attributes = [
        'no_dok'    => 0,
    ];

    // fillables/guarded
    protected $guarded = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    // =============================================
    // RELATIONS
    // =============================================
    public function cd() {
        return $this->belongsTo('App\CD', 'cd_header_id');
    }

    public function negara() {
        return $this->belongsTo('App\Negara', 'kd_negara_asal', 'kode');
    }

    public function lokasi(){
        return $this->belongsTo('App\Lokasi', 'lokasi_id');
    }

    // =============================================
    // TRAIT OVERRIDES
    // =============================================
    public function lock() {
        $cd = $this->cd;

        if (!$cd) {
            return false;
        }

        try {
            $cd->lock();
            return $this->traitLock();
        } catch (\Exception $e) {
            //throw $th;
            $this->unlock();
            return false;
        }
    }

    public function unlock() {
        return $this->traitUnlock();
    }

    //=================================================================================================
    // COMPUTED PROPERTIES GO HERE!!
    //=================================================================================================
    public function getJenisDokumenAttribute() {
        return 'spp';
    }

    public function getSkemaPenomoranAttribute()
    {
        return 'SPP/' . $this->lokasi->nama . '/SH';
    }

    public function getLinksAttribute() {
        $links = [
            [
                'rel'   => 'self',
                'uri'   => $this->uri,
            ]
        ];

        if ($this->cd) {
            $links[] = [
                'rel'   => 'cd',
                'uri'   => $this->cd->uri
            ];
        }

        return $links;
    }

    // Helpers
    static public function createFromCD(CD $cd) {
        if (!$cd) {
            return null;
        }

        // it's valid? must not be locked yet
        if ($cd->is_locked) {
            throw new \Exception("CD #{$cd->id} is locked already.");
        }

        // perhaps there's already st for this thing?
        if ($cd->spp) {
            throw new \Exception("CD #{$cd->id} is already postponed!");
        }

        // grab most recent kurs
        $first = $cd->getFirstValue();

        // $data_hitung    = $cd->simulasi_pungutan;

        // compute something
        $total_fob = $cd->getTotalValue('fob');
        $total_insurance = $cd->getTotalValue('insurance');
        $total_freight = $cd->getTotalValue('freight');
        $total_cif = $total_fob + $total_insurance + $total_freight;

        $kurs = Kurs::kode($first['valuta'])
                ->perTanggal(date('Y-m-d'))
                ->first();
        
        if (!$kurs) {
            throw new \Exception("Cannot find recent data for kurs {$first['valuta']}");
        }

        // create it
        $s = new SPP([
            'tgl_dok'   => date('Y-m-d'),
            'total_fob' => $total_fob,
            'total_insurance'   => $total_insurance,
            'total_freight'     => $total_freight,
            'total_cif' => $total_cif,
            'kurs_id'   => $kurs->id,
            'keterangan'        => "",
            'pemilik_barang'    => $cd->penumpang->nama
        ]);

        // link to cd
        $s->cd()->associate($cd);

        // return it
        return $s;
    }
}
