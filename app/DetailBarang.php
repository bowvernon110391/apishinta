<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;

class DetailBarang extends Model implements ISpecifiable, ITariffable
{
    use SoftDeletes;
    use TraitSpecifiable;
    use TraitTariffable;
    
    // settings
    protected $table = 'detail_barang';

    protected $guarded = [
        'created_at',
        'updated_at'
    ];

    protected $attributes = [
        'uraian' => '',
        'jumlah_kemasan' => 0,
        'jenis_kemasan' => 'PK',
        'fob' => 0.0,
        'insurance' => 0.0,
        'freight' => 0.0,
        'brutto' => 0.0
    ];

    // RELATIONSHIP
    public function header() {
        return $this->morphTo();
    }

    public function kurs() {
        return $this->belongsTo(Kurs::class, 'kurs_id');
    }

    public function hs() {
        return $this->belongsTo(HsCode::class, 'hs_id');
    }

    public function jenisKemasan() {
        return $this->belongsTo(Kemasan::class, 'jenis_kemasan', 'kode');
    }

    public function jenisSatuan() {
        return $this->belongsTo(Satuan::class, 'jenis_satuan', 'kode');
    }

    public function kategori() {
        return $this->belongsToMany(Kategori::class, 'detail_barang_kategori', 'detail_barang_id', 'kategori_id')->withTimestamps();
    }

    public function detailSekunder() {
        return $this->hasMany(DetailSekunder::class, 'detail_barang_id');
    }

    // ATTRIBUTES!!!

    public function getKategoriTagsAttribute() {
        return $this->kategori->map(function ($e) { return $e->nama; })->toArray();
    }

    // HELPER 
    // sync data with request ($d MUST BE EXISTING FOR SECONDARY DATA!!)
    public function syncPrimaryData(Request $r) {
        $this->uraian = expectSomething($r->get('uraian'), "Uraian Barang");
        $this->jumlah_kemasan = expectSomething($r->get('jumlah_kemasan'), "Jumlah Kemasan");
        $this->jenis_kemasan = expectSomething($r->get('jenis_kemasan'), "Jenis Kemasan");

        // data satuan optional
        $this->jumlah_satuan = $r->get('jumlah_satuan');
        $this->jenis_satuan = $r->get('jenis_satuan');

        // data nilai barang (isi default?)
        $this->fob = expectSomething($r->get('fob'), 'FOB');
        $this->insurance = expectSomething($r->get('insurance'), 'Insurance');
        $this->freight = expectSomething($r->get('freight'), 'Freight');

        $this->brutto = expectSomething($r->get('brutto'), 'Bruto');
        $this->netto = $r->get('netto');

        // associate the right kurs
        $kurs = $r->get('kurs');
        $kurs_id = $kurs['data']['id'] ?? null;
        $this->kurs()->associate(Kurs::findOrFail($kurs_id));

        // associate the right hs
        $hs = $r->get('hs');
        $hs_id = $hs['data']['id'] ?? null;
        $this->hs()->associate(HsCode::findOrFail($hs_id));
    }

    public function syncSecondaryData(Request $r) {
        if (!$this->exists()) {
            throw new \Exception("DetailBarang must be saved first or this operation will fail!");
        }

        // sync kategori tags here
        $kategori = Kategori::whereIn('nama', $r->get('kategori_tags', []))->get();
        $this->kategori()->sync($kategori);

        // sync detail sekunder data??
        $ds = $r->get('detailSekunder')['data'] ?? [];

        // 1st, update all that has data
        $toUpdate = array_filter($ds, function ($e) { return $e['id']; });

        foreach ($toUpdate as $s) {
            $data = DetailSekunder::findOrFail($s['id']);
            
            $data->data = $s['data'];
            $data->referensiJenisDetailSekunder()->associate(ReferensiJenisDetailSekunder::byName($s['jenis'])->first());
            $data->save();
        }

        // 2nd, delete the rest that is not included above
        $this->detailSekunder()->whereNotIn('id', $toUpdate)->delete();

        // 3rd, insert new detail sekunder
        $toInsert = array_filter($ds, function ($e) { return !$e['id']; });

        foreach ($toInsert as $s) {
            $data = new DetailSekunder();

            $data->data = $s['data'];
            $data->referensiJenisDetailSekunder()->associate(ReferensiJenisDetailSekunder::byName($s['jenis'])->first());

            $this->detailSekunder()->save($data);
        }
    }
}
