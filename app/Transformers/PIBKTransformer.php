<?php
namespace App\Transformers;

use App\PIBK;
use League\Fractal\TransformerAbstract;

class PIBKTransformer extends TransformerAbstract {
    protected $availableIncludes = [
        'importir',
        'airline',
        'pelabuhan_asal',
        'pelabuhan_tujuan',
        'pemberitahu',
        'source',
        'lokasi',
        
        'ndpbm',
        'status',
        'lampiran',
        'instruksi_pemeriksaan',
        
        'dokkap',
        
        'bppm',
        'billing',
        'sppb',

        'details'
    ];

    protected $defaultIncludes = [
        'importir',
        'airline',
        'pelabuhan_asal',
        'pelabuhan_tujuan',
        'pemberitahu',
        'source',
        'lokasi',
        
        'ndpbm',
        'status',
        // 'lampiran',
        // 'instruksi_pemeriksaan',
        
        // 'dokkap',
        
        'bppm',
        'billing',
        'sppb',

        // 'details'
    ];

    public function transform(PIBK $p) {
        // use common theme
        return [
            'id' => (int) $p->id,
            'no_dok' => (int) $p->no_dok,
            'tgl_dok' => $p->tgl_dok,
            'nomor_lengkap' => $p->nomor_lengkap_dok,
            
            // data barang
            'koli' => (int) $p->koli,

            // importir
            'importir_type' => $p->importir_type,

            // source
            'source_type' => $p->source_type,

            // bc11
            'no_bc11' => $p->no_bc11,
            'tgl_bc11' => $p->tgl_bc11,
            'pos_bc11' => $p->pos_bc11,
            'subpos_bc11' => $p->subpos_bc11,
            'subsubpos_bc11' => $p->subsubpos_bc11,

            // tarif
            'pph_tarif' => (float) $p->pph_tarif,

            // pelabuhan n flight
            'kd_pelabuhan_asal' => (string) $p->kd_pelabuhan_asal,
            'kd_pelabuhan_tujuan' => (string) $p->kd_pelabuhan_tujuan,

            'kd_airline' => (string) $p->kd_airline,
            'no_flight' => (string) $p->no_flight,

            // importir
            'alamat' => (string) $p->alamat,
            'npwp' => (string) $p->npwp,

            // is it locked?
            'is_locked' => $p->is_locked
        ];
    }

    // importir
    public function includeImportir(PIBK $p) {
        return $this->item($p->importir, spawnTransformer($p->importir));
    }

    // airline
    public function includeAirline(PIBK $p) {
        return $this->item($p->airline, new AirlineTransformer);
    }

    // 'pelabuhan_asal',
    public function includePelabuhanAsal(PIBK $p) {
        return $this->item($p->pelabuhanAsal, new PelabuhanTransformer);
    }

    // 'pelabuhan_tujuan',
    public function includePelabuhanTujuan(PIBK $p) {
        return $this->item($p->pelabuhanTujuan, new PelabuhanTransformer);
    }

    // 'pemberitahu', optional
    public function includePemberitahu(PIBK $p) {
        $pp = $p->pemberitahu;
        if ($pp) {
            return $this->item($pp, spawnTransformer($pp));
        }
    }

    // 'source', optional
    public function includeSource(PIBK $p) {
        $s = $p->source;
        if ($s) {
            return $this->item($s, spawnTransformer($s));
        }
    }

    // 'lokasi', optional
    public function includeLokasi(PIBK $p) {
        $l = $p->lokasi;
        if ($l) {
            return $this->item($l, spawnTransformer($l));
        }
    }
    
    // 'ndpbm',
    public function includeNdpbm(PIBK $p) {
        $n = $p->ndpbm;
        if ($n) {
            return $this->item($n, new KursTransformer);
        }
    }

    // 'status',
    public function includeStatus(PIBK $p) {
        return $this->collection($p->status, new StatusTransformer);
    }

    // 'lampiran',
    public function includeLampiran(PIBK $p) {
        return $this->collection($p->lampiran, new LampiranTransformer);
    }

    // 'instruksi_pemeriksaan', optional
    public function includeInstruksiPemeriksaan(PIBK $p) {
        $ip = $p->instruksiPemeriksaan;
        if ($ip) {
            return $this->item($ip, new InstruksiPemeriksaanTransformer);
        }
    }
    
    // 'dokkap',
    public function includeDokkap(PIBK $p) {
        return $this->collection($p->dokkap, new DokkapTransformer);
    }
    
    // 'bppm', optional
    public function includeBppm(PIBK $p) {
        $b = $p->bppm;
        if ($b) {
            return $this->item($b, new BPPMTransformer);
        }
    }

    // 'billing', optional
    public function includeBilling(PIBK $p) {
        return $this->collection($p->billing, new BillingTransformer);
    }

    // 'sppb', optional
    public function includeSppb(PIBK $p) {
        $s = $p->sppb;
        if ($s) {
            return $this->item($s, new SPPBTransformer);
        }
    }

    // 'details'
    public function includeDetails(PIBK $p) {
        return $this->collection($p->detailBarang, new DetailBarangTransformer);
    }
}