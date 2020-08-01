<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;
use ReflectionClass;

class BPPM extends AbstractDokumen implements ILinkable
{
    protected $table = 'bppm';

    protected $guarded = [
        'created_at',
        'updated_at'
    ];

    protected $attribute = [
        'no_dok'    => 0
    ];

    // ==================RELATIONS============================================
    
    public function payable() {
        return $this->morphTo();
    }

    public function pejabat() {
        return $this->belongsTo(SSOUserCache::class, 'pejabat_id', 'user_id');
    }
    
    // ==================ATTRIBUTES============================================
    public function getJenisDokumenAttribute(){
        return 'bppm';
    }
    
    public function getJenisDokumenLengkapAttribute(){
        return 'Bukti Penerimaan Pembayaran Manual';
    }
    public function getSkemaPenomoranAttribute(){
        return 'BPPM';
    }

    // Skema penomoran BPPM
    protected function formatBppmSequence($nomor, $sqlDate, $kode_kantor) {
        $monthLetter = [
            '01'    => 'A',
            '02'    => 'B',
            '03'    => 'C',
            '04'    => 'D',
            '05'    => 'E',
            '06'    => 'F',
            '07'    => 'G',
            '08'    => 'H',
            '09'    => 'I',
            '10'    => 'J',
            '11'    => 'K',
            '12'    => 'L',
        ];

        $month  = substr($sqlDate, 5, 2);
        
        $part1  = substr($sqlDate, 2, 2);
        $part2  = $kode_kantor;
        $part3  = $monthLetter[$month];
        $part4  = str_pad($nomor, 7, '0', STR_PAD_LEFT);

        return $part1.$part2.$part3.$part4;
    }

    // BPPM punya skema penomoran sendiri
    public function getNomorLengkapAttribute() {
        if ($this->no_dok == 0) {
            return null;
        } else if (strlen($this->nomor_lengkap_dok) > 1) {
            return $this->nomor_lengkap_dok;
        }
        
        $nomorLengkap = $this->formatBppmSequence($this->no_dok, $this->tgl_dok, $this->kode_kantor);

        return $nomorLengkap;
    }

    
    public function getLinksAttribute() {
        $links = [
            [
                'rel'   => 'self',
                'uri'   => $this->uri,
            ]
        ];

        return $links;
    }

    // ====================SCOPES===================================================
    public function scopeWhereHasBilling($query, bool $state) {
        /* if (!count(BPPM::$payableClasses)) {
            BPPM::$payableClasses = BPPM::listAllPayableClasses();
        } */

        return $query->whereHasMorph('payable', BPPM::$payableClasses, function ($q) use ($state) {
            if (!$state)
                $q->notBilled();
            else
                $q->billed();
        });
    }

    public function scopeByQuery($query, $q='', $from, $to) {
        // by nomor lengkap maybe? or by payer?
        return $query->where('nomor_lengkap_dok', 'like', "%$q%")
                ->orWhereHas('pejabat', function ($q1) use ($q) {
                    $q1->where('name', 'like', "%$q%");
                })
                ->when($from, function ($q1) use ($from) {
                    $q1->where('tgl_dok', '>=', $from);
                })
                ->when($to, function ($q1) use ($to) {
                    $q1->where('tgl_dok', '<=', $to);
                });    
    }

    // deep search (search on payable data)
    public function scopeDeepQuery($query, $q, $from, $to) {
        return $query->whereHasMorph('payable', BPPM::$payableClasses, function ($q1) use ($q, $from, $to) {
            $q1->byQuery($q, $from, $to);
        });
    }

    protected static $payableClasses = [
        CD::class,
        PIBK::class
    ];

    static public function listAllPayableClasses() {
        $cs = get_declared_classes();

        $r = array_filter($cs, function($e) { 
            $ref = new ReflectionClass($e);
            return $ref->implementsInterface("App\\IPayable");
        });
        return array_values($r);
    }
}
