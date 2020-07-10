<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;

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
    public function scopeNotBilled($query) {
        return $query->whereHasMorph('payable', function ($q) {
            $q->notBilled();
        });
    }
}
