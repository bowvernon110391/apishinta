<?php

namespace App;


class PIBK extends AbstractDokumen implements
IInstructable, IHasGoods, ISpecifiable, ITariffable,
IHasPungutan, INotable, IPayable, IGateable
{
    // use default implementation (traits)
    use TraitInstructable;

    use TraitSpecifiable;

    use TraitHasGoods;
    use TraitTariffable;
    use TraitHasPungutan;

    use TraitHasDokkaps;
    use TraitNotable;
    use TraitPayable;
    use TraitGateable;

    // settings
    protected $table = 'pibk';

    protected $guarded = [
        'created_at',
        'updated_at'
    ];

    // default attributes
    protected $attributes = [
        'no_dok' => 0,
        'npwp' => '',
        'alamat' => '',
        'no_flight' => '',
        'koli' => 1
    ];

    // relations
    public function importir() {
        return $this->morphTo();
    }

    public function pemberitahu() {
        return $this->morphTo();
    }

    public function source() {
        return $this->morphTo();
    }

    public function lokasi() {
        return $this->morphTo();
    }

    public function airline() {
        return $this->belongsTo(Airline::class, 'kd_airline', 'kode');
    }

    public function ndpbm() {
        return $this->belongsTo(Kurs::class, 'ndpbm_id');
    }

    public function pelabuhanAsal() {
        return $this->belongsTo(Pelabuhan::class, 'kd_pelabuhan_asal', 'kode');
    }

    public function pelabuhanTujuan() {
        return $this->belongsTo(Pelabuhan::class, 'kd_pelabuhan_tujuan', 'kode');
    }

    // scopes

    // attributes
    public function getJenisDokumenAttribute()
    {
        return 'pibk';
    }

    public function getJenisDokumenLengkapAttribute()
    {
        return 'Pemberitahuan Impor Barang Khusus (BC 2.1)';
    }

    public function getSkemaPenomoranAttribute()
    {
        return 'PIBK';
    }

    public function getJenisPenerimaanAttribute()
    {
        return 'IMPOR';
    }

    public function getPayerAttribute()
    {
        // generate data based on available data
        if ($this->importir instanceof Penumpang) {
            // grab nama
            $nama = $this->importir->nama;
            // nomor identitas
            // bisa npwp/paspor
            $no_identitas = $this->npwp ?? $this->importir->no_paspor;
            $jenis_identitas = $this->npwp ? 'NPWP' : 'PASPOR';
            $npwp = $this->npwp;
        }

        return [
            'nama' => $nama,
            'no_identitas' => $no_identitas,
            'jenis_identitas' => $jenis_identitas,
            'npwp' => $npwp
        ];
    }

    public function getDataSppbAttribute()
    {
        $total_brutto = $this->detailBarang()
                    ->isPenetapan()
                    ->get()
                    ->reduce(function($acc, $e) {
                        return $acc + $e->brutto;
                    }, 0.0);

        // ambil hawb klo ada
        $hawb = $this->dokkap()->byKode(740)->first();

        // pemberitahu? default
        $pemberitahu = [
            'npwp' => '-',
            'nama' => '-'
        ];

        if ($this->pemberitahu) {
            $pemberitahu['npwp'] = $this->pemberitahu->npwp;
            $pemberitahu['nama'] = $this->pemberitahu->nama;
        }

        return [
            'pemberitahu' => $pemberitahu,

            'importir' => $this->payer,

            'sarana_pengangkut' => $this->airline->uraian,
            'no_flight' => $this->no_flight,
            'jumlah_jenis_kemasan' => $this->koli . ' Koli',
            'brutto' => $total_brutto,

            'no_bc11' => $this->no_bc11 ?? '-',
            'tgl_bc11' => $this->tgl_bc11 ?? '-',
            'pos_bc11' => $this->pos_bc11 ?? '-',
            'subpos_bc11' => $this->subpos_bc11 ?? '-',
            'subsubpos_bc11' => $this->subsubpos_bc11 ?? '-',

            'hawb' => [
                'nomor' => $hawb->nomor_lengkap_dok ?? '-',
                'tanggal' => $hawb->tgl_dok ?? '-'
            ]
        ];
    }

    // nomor lengkap, e.g. 000001/CD/T2F/SH/2019
    public function getNomorLengkapAttribute() {
        if ($this->no_dok == 0) {
            return null;
        }

        if (strlen($this->nomor_lengkap_dok) > 0) {
            return $this->nomor_lengkap_dok;
        }
        
        $nomorLengkap = str_pad($this->no_dok, 6,"0", STR_PAD_LEFT);
        return $nomorLengkap;
    }

    // scopes
    public function scopeByQuery($query, $q, $from=null, $to=null) {
        return $query->where('npwp', 'like', "%$q%")
        ->orWhere('alamat', 'like', "%$q%")
        ->orWhere('no_flight', 'like', "%$q%")
        ->orWhere('nomor_lengkap_dok', 'like', "%$q%")
        ->orWhere(function($q1) use ($q) {
            $q1->byImportir($q);
        })
        ->when($from, function($q1) use ($from) {
            $q1->from($from);
        })
        ->when($to, function($q1) use ($to) {
            $q1->to($to);
        })
        ->latest()
        ->orderBy('id', 'desc')
        ;
    }

    public function scopeFrom($query, $from) {
        return $query->where('tgl_dok', '>=', $from);
    }

    public function scopeTo($query, $to) {
        return $query->where('tgl_dok', '<=', $to);
    }

    public function scopeByImportir($query, $q) {
        return $query->whereHasMorph('importir', [Penumpang::class], function($q1) use ($q) {
            $q1->where('nama', 'like', "%$q%");
        });
    }
}
