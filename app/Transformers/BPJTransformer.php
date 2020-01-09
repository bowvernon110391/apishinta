<?php
namespace App\Transformers;

use App\BPJ;
use League\Fractal\TransformerAbstract;

class BPJTransformer extends TransformerAbstract {
    // defaultly loaded relations
    protected $defaultIncludes = [
        'penumpang'
    ];

    // available relations, default relations not needed to apply
    protected $availableIncludes = [
        'penumpang',
        'guaranteeable'
    ];

    // basic transformation, without any sweetener
    public function transform(BPJ $bpj) {
        $result = [
            'id'                => $bpj->id,
            'no_dok'            => $bpj->no_dok,
            'tgl_dok'           => $bpj->tgl_dok,
            'nomor_lengkap'     => $bpj->nomor_lengkap,
            // 'tanggal'           => $bpj->tanggal,
            'jenis_identitas'   => $bpj->jenis_identitas,
            'no_identitas'      => $bpj->no_identitas,
            'alamat'            => $bpj->alamat,
            'nomor_jaminan'     => $bpj->nomor_jaminan,
            'tanggal_jaminan'   => $bpj->tanggal_jaminan,
            'penjamin'          => $bpj->penjamin,
            'alamat_penjamin'   => $bpj->alamat_penjamin,
            'bentuk_jaminan'    => $bpj->bentuk_jaminan,
            'jumlah'            => (float) $bpj->jumlah,
            'jenis'             => $bpj->jenis,
            'tanggal_jatuh_tempo'   => $bpj->tanggal_jatuh_tempo,
            'nip_pembuat'       => $bpj->nip_pembuat,
            'nama_pembuat'      => $bpj->nama_pembuat,
            'active'            => (bool) $bpj->active,
            'status'            => $bpj->status,
            'no_bukti_pengembalian' => $bpj->no_bukti_pengembalian,
            'tgl_bukti_pengembalian'=> $bpj->tgl_bukti_pengembalian,
            'kode_agenda'       => $bpj->kode_agenda,
            'catatan'           => $bpj->catatan,

            'lokasi'            => $bpj->lokasi->nama
        ];

        return $result;
    }

    // include penumpang?
    public function includePenumpang(BPJ $bpj) {
        $penumpang = $bpj->penumpang;
        // cmn ada satu penumpang, perlakukan sbg item tunggal
        return $this->item($penumpang, new PenumpangTransformer);
    }

    // include guaranteeable
    public function includeGuaranteeable(BPJ $bpj) {
        if (!$bpj->guaranteeable_id) {
            return null;
        }

        $dokSumber = $bpj->guaranteeable;

        // tergantung sumbernya, bisa macem2
        $className = get_class($dokSumber);

        switch ($className) {
            case "App\CD":
            return $this->item($dokSumber, new CDTransformer);
        }

        return null;
    }
}

?>