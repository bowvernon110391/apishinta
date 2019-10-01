<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SSPCP extends Model implements IDokumen
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

    
}
