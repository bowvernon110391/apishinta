<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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

        if($cd->is_locked)
            return false;
        
        if($this->is_locked)
            return $this->is_locked;
        
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
    static public function createFromCD(CD $cd, $keterangan) {
        // check if cd is valid
        if (!$cd) {
            throw new \Exception("Cannot create SPPBMCP from invalid CD!");
        }

        // check if cd already has a sspcp?
        if ($cd->sspcp) {
            throw new \Exception("CD sudah ditetapkan! batalkan dahulu penetapannya apabila akan ditetapkan ulang!");
        }

        // first, spawn SPPBMCP, and copy all data from header
        $pungutan = $cd->simulasi_pungutan;

        if (!$pungutan) {
            throw new \Exception("Gagal melakukan perhitungan pungutan untuk CD!");
        }

        $tarif_pph = $pungutan['pph_tarif'];    // one for all (for now, we don't handle special case)

        $sspcp = new SSPCP();

        // fill important fields
        $sspcp->tgl_dok = date('Y-m-d');
        $sspcp->total_bm = $pungutan['total_bm'];
        $sspcp->total_ppn = $pungutan['total_ppn'];
        $sspcp->total_pph = $pungutan['total_pph'];
        $sspcp->total_ppnbm = $pungutan['total_ppnbm'];

        // default shit
        $sspcp->total_fob = 0;
        $sspcp->total_freight = 0;
        $sspcp->total_insurance = 0;
        $sspcp->total_cif = 0;
        $sspcp->total_nilai_pabean = 0;
        
    }
}
