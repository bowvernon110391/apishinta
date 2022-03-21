<?php

namespace App;

use Illuminate\Http\Request;

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

    public function spp() {
        // return $this->hasOne('App\SPP', 'cd_header_id');
        return $this->morphOne('App\SPP', 'source');
    }

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
            $no_identitas = $this->importir->no_paspor;
            $jenis_identitas = 'PASPOR';
            $npwp = $this->npwp;
        }

        return [
            'nama' => $nama,
            'no_identitas' => $no_identitas,
            'alamat' => $this->alamat,
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

        $summary_package = $this->summaryPackage($this->detailBarang);
        $tmp = [];
        foreach ($summary_package as $jenis => $jumlah) {
            $tmp[] = "{$jumlah} {$jenis}";
        }
        $jumlah_jenis_kemasan = implode(", ", $tmp);

        return [
            'pemberitahu' => $pemberitahu,

            'importir' => $this->payer,

            'sarana_pengangkut' => $this->airline->uraian,
            'no_flight' => $this->no_flight,
            'jumlah_jenis_kemasan' => $jumlah_jenis_kemasan,
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
    public function scopePure($query) {
        return $query->doesntHave('spp');
    }

    public function scopeByQuery($query, $q='', $from=null, $to=null) {
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

    public function scopeTanpaPemberitahu($query) {
        return $query->where('pemberitahu_id', 0)
                    ->orWhereNull('pemberitahu_id');
    }

    // functions (HELPERS, mostly)

    /**
     * static createFromSource
     *  create PIBK dari source object
     * @$r : request object
     * @$s : source. for now only support (SPP and ST)
     */
    static public function createFromSource(Request $r, $s) : PIBK {
        // it doesn't have one. let's create it
        $pemberitahu_type = $r->get('pemberitahu_type');
        $pemberitahu_id = $r->get('pemberitahu_id');
        $lokasi_type = expectSomething($r->get('lokasi_type'), 'Jenis Lokasi Barang');
        $lokasi_id = expectSomething($r->get('lokasi_id'), 'ID Lokasi Barang');

        $p = new PIBK([
            'tgl_dok' => date('Y-m-d'),
            'pemberitahu_type' => $pemberitahu_type,
            'pemberitahu_id' => $pemberitahu_id,

            'lokasi_type' => $lokasi_type,
            'lokasi_id' => $lokasi_id,

            'npwp' => $s->source->npwp ?? '',
            'alamat' => $s->source->alamat ?? '',

            'tgl_kedatangan' => $s->source->tgl_kedatangan,
            'no_flight' => $s->source->no_flight,
            'kd_airline' => $s->source->kd_airline,

            'kd_pelabuhan_asal' => $s->source->kd_pelabuhan_asal,
            'kd_pelabuhan_tujuan' => $s->source->kd_pelabuhan_tujuan,

            'pph_tarif' => $s->source->pph_tarif ?? 15.0
        ]);

        // gotta grab kurs
        $ndpbm = Kurs::perTanggal(date('Y-m-d'))->kode('USD')->first();

        if (!$ndpbm) {
            throw new \Exception("Kurs NDPBM invalid. Pastikan data kurs up-to-date");
        }

        // associate with importir, source and ndpbm
        $p->importir()->associate($s->source->penumpang);
        $p->source()->associate($s);
        $p->ndpbm()->associate($ndpbm);

        // save it
        $p->save();

        // duplicate data barang? di controller aja?
        // copy barang dan dokkap dari cd
        $p->copyDetailBarang($s->source);
        $p->copyDokkap($s->source);

        // so just log it
        AppLog::logInfo(
            "PIBK #{$p->id} diterbitkan dari SPP #{$s->id} oleh {$r->userInfo['username']}",
            $p
        );

        // append status utk source dan pibknya
        // append status pibk
        $p->appendStatus(
            'CREATED',
            null,
            "PIBK #{$p->id} diterbitkan dari " . class_basename($s) ." #{$s->id} nomor {$s->nomor_lengkap_dok} tanggal {$s->tgl_dok}",
            $s,
            null,
            SSOUserCache::byId($r->userInfo['user_id'])
        );
        // append status source?
        $s->appendStatus(
            'PIBK',
            null,
            "Penerbitan PIBK #{$p->id} sebagai penyelesaian atas " . class_basename($s),
            $p,
            null,
            SSOUserCache::byId($r->userInfo['user_id'])
        );

        return $p;
    }
}
