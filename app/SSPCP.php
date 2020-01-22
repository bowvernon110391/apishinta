<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class SSPCP extends Model implements IDokumen, ILinkable
{
    //
    use TraitDokumen {
        lock as public traitLock;
        unlock as public traitUnlock;
    }
    use TraitLoggable;
    use SoftDeletes;

    protected $table = 'sspcp_header';

    protected $guarded = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $attribute = [
        'no_dok'    => 0
    ];

    public function details(){
        return $this->hasMany('App\DetailSSPCP', 'sspcp_header_id');
    }

    public function cd(){
        return $this->belongsTo('App\CD','cd_header_id');
    }
    
    public function lokasi(){
        return $this->belongsTo('App\Lokasi', 'lokasi_id');
    }

    public function getJenisDokumenAttribute(){
        return 'sspcp';
    }

    public function getSkemaPenomoranAttribute(){
        return 'SSPCP/'. $this->lokasi->nama . '/SH';
    }

    public function lock(){
        $cd = $this->cd;

        if($this->is_locked)
            return $this->is_locked;

        /* if($cd->is_locked)
            return false; */
        
        return $cd->lock() && $this->traitLock();        
    }

     public function unlock(){
        // $cd = $this->cd;

        // if(!$cd->is_locked)
        //     return false;
        
        // if(!$this->is_locked)
        //     return !$this->is_locked;
        
        // return $cd->unlock() && $this->traitUnlock();        
        return $this->traitUnlock();
    }

    
    public function getLinksAttribute() {
        $links = [
            [
                'rel'   => 'self',
                'uri'   => $this->uri,
            ],
            [
                'rel'   => 'self.details',
                'uri'   => $this->uri . '/details'
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

    // STATIC SPECIAL FUNCTIONS
    static public function createFromCD(CD $cd, $keterangan, $lokasi_id, $nama_pejabat, $nip_pejabat) {
        // check if cd is valid
        if (!$cd) {
            throw new \Exception("Cannot create SPPBMCP from invalid CD!");
        }

        // check if cd already has a sspcp?
        if ($cd->sspcp) {
            throw new \Exception("CD sudah ditetapkan! batalkan dahulu penetapannya apabila akan ditetapkan ulang!");
        }

        // check if lokasi exist
        $lokasi = Lokasi::findOrFail($lokasi_id);

        // first, spawn SPPBMCP, and copy all data from header
        $pungutan = $cd->simulasi_pungutan;

        if (!$pungutan) {
            throw new \Exception("Gagal melakukan perhitungan pungutan untuk CD!");
        }

        $tarif_pph = $pungutan['pph_tarif'];    // one for all (for now, we don't handle special case)
        $tarif_ppn = 0.1;

        $sspcp = new SSPCP();

        // fill important fields
        $sspcp->no_dok = 0;
        $sspcp->tgl_dok = date('Y-m-d');
        $sspcp->total_bm = $pungutan['total_bm'];
        $sspcp->total_ppn = $pungutan['total_ppn'];
        $sspcp->total_pph = $pungutan['total_pph'];
        $sspcp->total_ppnbm = $pungutan['total_ppnbm'];
        // $sspcp->total_denda = null;

        // associate lokasi
        $sspcp->lokasi()->associate($lokasi);

        // set keterangan
        $sspcp->keterangan = $keterangan;

        // set data pejabat
        $sspcp->nama_pejabat = $nama_pejabat;
        $sspcp->nip_pejabat = $nip_pejabat;

        // set penumpang = importir
        $sspcp->nama_importir = $cd->penumpang->nama;

        // set data kurs
        $sspcp->kode_valuta = $cd->ndpbm->kode_valas;
        $sspcp->nilai_valuta = $cd->ndpbm->kurs_idr;

        $cd->sspcp()->save($sspcp);

        // $sspcp = $cd->sspcp;

        // set nomor sspcp?
        $sspcp->setNomorDokumen();

        // tambahkan detil
        foreach ($cd->details as $cdd) {
            $tarif_bm = $pungutan['komersil'] ? (float) $cdd->hs->bm_tarif : 10.0;
            $tarif_ppn = 10.0;
            $tarif_ppnbm = (float) $cdd->ppnbm_tarif;

            $beaMasuk = $cdd->beaMasuk($tarif_bm);

            $det = new DetailSSPCP([
                'cd_detail_id'  => $cdd->id,
                'fob'  => $cdd->fob,
                'freight'  => $cdd->freight,
                'insurance'  => $cdd->insurance,
                'cif'  => $cdd->cif,
                'nilai_pabean'  => $cdd->nilai_pabean,
                // 'pembebasan'  => 0,
                'trf_bm'  => $tarif_bm,
                'trf_ppn'  => $tarif_ppn,
                'trf_ppnbm'  => $tarif_ppnbm,
                'trf_pph'  => $tarif_pph,
                'bm'  => $beaMasuk,
                'ppn'  =>  $cdd->ppn($beaMasuk, $tarif_ppn),
                'ppnbm'  =>  $cdd->ppnbm($beaMasuk, $tarif_ppnbm),
                'pph'  =>  $cdd->pph($beaMasuk, $tarif_pph),
                'denda'=>  0,
                // 'keterangan' => $faker->sentence(10),
                'kode_valuta'  => $cdd->kurs->kode_valas,
                'hs_code'  => $cdd->hs->kode,
                'nilai_valuta' => $cdd->kurs->kurs_idr,
                // 'brutto' => $faker->randomFloat(5, 500),
                // 'netto' => $faker->randomFloat(5, 500),
            ]);

            // save it
            $sspcp->details()->save($det);
        }

        // return sspcp
        return $sspcp;
    }
}
