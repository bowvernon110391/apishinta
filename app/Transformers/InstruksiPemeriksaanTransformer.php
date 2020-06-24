<?php
namespace App\Transformers;

use App\InstruksiPemeriksaan;
use League\Fractal\TransformerAbstract;

class InstruksiPemeriksaanTransformer extends TransformerAbstract {

    protected $availableIncludes = [
        'instructable'
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

            'instructable_uri'  => $ip->instructable ? $ip->instructable->uri : null
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
}