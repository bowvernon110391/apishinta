<?php
namespace App\Transformers;

use App\InstruksiPemeriksaan;
use League\Fractal\TransformerAbstract;

class InstruksiPemeriksaanTransformer extends TransformerAbstract {

    protected $availableIncludes = [
        'instructable',
        'lhp'
    ];

    protected $defaultIncludes = [
        'lhp'
    ];

    public function transform(InstruksiPemeriksaan $ip) {
        return [
            'id'        => (int) $ip->id,
            'no_dok'    => (int) $ip->no_dok,
            'tgl_dok'   => (string) $ip->tgl_dok,
            'nomor_lengkap' => $ip->nomor_lengkap,

            'nama_issuer' => $ip->nama_issuer,
            'nip_issuer' => $ip->nip_issuer,

            'jumlah_periksa' => $ip->jumlah_periksa,
            'ajukan_contoh' => $ip->ajukan_contoh,
            'ajukan_foto' => $ip->ajukan_foto,
            'pemeriksa_id' => $ip->pemeriksa_id,

            'instructable_uri'  => $ip->instructable ? $ip->instructable->uri : null,

            'instructable_type' => $ip->instructable_type,
            'instructable_nomor_lengkap' => $ip->instructable->nomor_lengkap_dok ?? '',

            'last_status' => $ip->short_last_status,
            'is_locked' => $ip->is_locked
        ];
    }

    public function includeInstructable(InstruksiPemeriksaan $ip) {
        $instructable = $ip->instructable;

        if ($instructable) {
            $transformerName = "App\\Transformers\\" . class_basename($instructable) . 'Transformer';

            if (class_exists($transformerName)) {
                return $this->item($instructable, new $transformerName);
            }
            throw new \Exception("Error shieeet " . $transformerName);
        }

    }

    public function includeLhp(InstruksiPemeriksaan $ip) {
        $lhp = $ip->lhp;
        if ($lhp) {
            return $this->item($lhp, new LHPTransformer);
        }
    }
}