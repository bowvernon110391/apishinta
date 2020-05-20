<?php
namespace App;

/**
 * 
 */
trait TraitDokumen
{
    // all dokumen can have lampiran so use its trait
    use TraitAttachable;

    // default locking mechanism
    public function lock(){
        // if we're locked, do nothing
        if ($this->is_locked)
            return $this->is_locked;

        // bagusnya dikasih transaction guard biar 
        // databasenya selalu ACID
        
        $this->appendStatus('CLOSED');
        $this->setNomorDokumen();
        return $this->is_locked;
    }

    public function unlock(){
        if(!$this->is_locked)
            return !$this->is_locked;

        $this->appendStatus('OPEN');
        return !$this->is_locked;
    }

    public function appendStatus($name, $lokasi = null) {
        $this->status()->save(
            Status::create(['status' => $name, 'lokasi' => $lokasi])
        );
    }

    public function getIsLockedAttribute(){
        return isset($this->last_status) ? $this->last_status->status == 'CLOSED' : false; 
    }

    public function setNomorDokumen($force = false){
        if (!$force) {
            if ($this->no_dok != 0) {
                return ;
            }
        }

        // set no_dok
        $this->no_dok = getSequence($this->skema_penomoran, $this->tahun_dok);
        // set nomor_lengkap_dok using nomor_lengkap attribute
        $this->nomor_lengkap_dok = $this->nomor_lengkap;

        // $doctype = get_class($this);
        // echo "Setting doc number of {$doctype} using {$this->no_dok} and {$this->nomor_lengkap}\n";
        // save it
        $this->save();

        // if this got no status yet, create it
        $last_status = $this->last_status;

        if (!$last_status) {
            $this->appendStatus('CREATED');
        }
    }

    //=================================================================================================
    // COMPUTED PROPERTIES GO HERE!!
    //=================================================================================================
    // ambil data tahun dok dari tgl
    public function getTahunDokAttribute() {
        return (int)substr($this->tgl_dok, 0, 4);
    }

    // nomor lengkap, e.g. 000001/CD/T2F/SH/2019
    public function getNomorLengkapAttribute() {
        if ($this->no_dok == 0) {
            return null;
        }
        
        $nomorLengkap = str_pad($this->no_dok, 6,"0", STR_PAD_LEFT)
                        .'/'
                        .$this->skema_penomoran
                        .'/'
                        .$this->tahun_dok;
        return $nomorLengkap;
    }

    public function getLastStatusAttribute(){
        return $this->status()->latest()->orderBy('id', 'desc')->first();
    }

    public function getShortLastStatusAttribute() {
        $ls = $this->last_status;
        if ($ls) {
            return [
                'status'    => $ls->status,
                'created_at'=> (string) $ls->created_at
            ];
        }

        return null;
    }

    public function getUriAttribute() {
        return "/{$this->jenis_dokumen}/{$this->id}";
    }

    /**
     * RELATIONS (injected to all document)
     */

    public function status() {
        return $this->morphMany('App\Status', 'statusable');
    }

    public function statusOrdered() {
        return $this->status()->latest()->orderBy('id', 'desc')->get();
    }

    public function billing() {
        return $this->morphMany('App\Billing', 'billable');
    }

    public function bpj() {
        return $this->morphOne('App\BPJ', 'guaranteeable');
    }

    public function dokkap() {
        return $this->morphMany('App\Dokkap', 'master');
    }

    /**
     * SCOPES (injected to every relevant class)
     */
    public function scopeByLastStatus($query, $status) {
        // first fetch all BPJ's last status
        $dokIds = Status::latestPerDoctype()
                        ->byDocType(get_class())
                        ->byStatus($status)
                        ->select(['status.statusable_id'])
                        ->get();
        // now find all bpj's whose id is in that
        return $query->whereIn('id', $dokIds);
    }

    public function scopeByLastStatusOtherThan($query, $status) {
        // first fetch all BPJ's last status
        $dokIds = Status::latestPerDoctype()
                        ->byDocType(get_class())
                        ->byStatusOtherThan($status)
                        ->select(['status.statusable_id'])
                        ->get();
        // now find all bpj's whose id is in that
        return $query->whereIn('id', $dokIds);
    }
}
