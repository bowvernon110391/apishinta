<?php

namespace App\Http\Controllers;

use App\Airline;
use App\AppLog;
use App\DetailSekunder;
use App\Gudang;
use Illuminate\Http\Request;
use App\Negara;
use App\HsCode;
use App\Kategori;
use App\Kemasan;
use App\Lokasi;
use App\Pelabuhan;
use App\PJT;
use App\Satuan;
use App\ReferensiJenisDetailSekunder;
use App\ReferensiJenisDokkap;
use App\ReferensiJenisPungutan;
use App\Services\SSO;
use App\TPS;
use App\Transformers\AirlineTransformer;
use App\Transformers\GudangTransformer;
use App\Transformers\HsCodeTransformer;
use App\Transformers\NegaraTransformer;
use App\Transformers\KategoriTransformer;
use App\Transformers\KemasanTransformer;
use App\Transformers\LokasiTransformer;
use App\Transformers\PelabuhanTransformer;
use App\Transformers\PJTTransformer;
use App\Transformers\ReferensiJenisDetailSekunderTransformer;
use App\Transformers\ReferensiJenisDokkapTransformer;
use App\Transformers\ReferensiJenisPungutanTransformer;
use App\Transformers\SatuanTransformer;
use App\Transformers\TPSTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use League\Fractal\Manager;
use PDOException;

class ReferensiController extends ApiController
{
    public function __construct(Manager $mgr, Request $r, SSO $sso)
    {
        parent::__construct($mgr, $r);
        $this->sso = $sso;
    }
    // GET /negara
    public function getAllNegara(Request $r) {
        $negara = Negara::all();
        return $this->respondWithCollection($negara, new NegaraTransformer);
    }

    // GET /negara/{kode}
    public function getNegaraByCode($kode) {
        $negara = Negara::byExactCode($kode)->first();

        if (!$negara) {
            return $this->errorNotFound("Negara dengan kode '{$kode}' tidak ditemukan");
        }
        // respond with item
        return $this->respondWithItem($negara, new NegaraTransformer);
    }

    // POST /negara
    public function storeNegara(Request $r) {
        // must do some storing here
        try {
            // must be of json type
            if (!$r->isJson()) {
                throw new \Exception("Harus berupa json!");
            }

            $id = expectSomething($r->get('id'), "Country's numeric ID");
            $kode = expectSomething($r->get('kode'), "Country's 2 alphabetical code");
            $kode_alpha3 = expectSomething($r->get('kode_alpha3'), "Country's 3 alphabetical code");
            $uraian = expectSomething($r->get('uraian'), "Nama Negara");

            // try to save
            $n = new Negara;
            $n->id = $id;
            $n->kode = $kode;
            $n->kode_alpha3 = $kode_alpha3;
            $n->uraian = $uraian;

            $n->save();

            // log first
            AppLog::logInfo("Negara '{$n->uraian}' ditambahkan oleh {$r->userInfo['username']}", $n);

            // return array
            return $this->respondWithArray([
                'id'    => $n->kode,
                'uri'   => '/penumpang/' . $n->kode
            ]);
        } catch (\Exception $e) {
            return $this->errorBadRequest("Gagal Menyimpan data negara: ".$e->getMessage());
        }
    }

    // GET /hs
    public function getHS(Request $r) {
        // can only use q
        $q = $r->get('q', '');

        // by default, select all
        $query = HsCode::orderBy('id');

        // depending on the type, execute different query
        // if query is number, e.g. 0320 then we do query all
        // children node
        if (preg_match('/^([0][1-9]|[1-9][0-9])\d{0,8}$/', $q)) {
            // kode mode
            $query = $query->byHs($q);
        } else if (strlen($q)) {
            $query = HsCode::queryWildcard($q);
        }

        // build paginator
        $paginator = $query->paginate($r->get('number'))
                            ->appends($r->except('page'));
        
        return $this->respondWithPagination($paginator, new HsCodeTransformer);
    }

    // GET /hs/{id}
    public function getHSById(Request $r, $id) {
        try {
            $hs = HsCode::findOrFail($id);

            return $this->respondWithItem($hs, new HsCodeTransformer);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound("HS #{$id} was not found");
        } catch (\Exception $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }

    // GET /kategori
    public function getKategori(Request $r) {
        $q = $r->get('q');

        $query = Kategori::orderBy('nama');

        if ($q) {
            // if it contains query, grab all
            // possible match
            $query = $query->byName($q);
        }

        return $this->respondWithCollection($query->get(), new KategoriTransformer);
    }

    // POST /kategori
    public function storeKategori(Request $r) {
        $nama = $r->get('nama');
        // only json allowed
        if (!$r->isJson()) {
            return $this->errorBadRequest("Only json allowed dude");
        }
        // gotta check if nama is valid
        if (!$nama) {
            return $this->errorBadRequest("Nama Kategori tidak valid -> {$nama}");
        }
        // gotta check if name already exists though
        if (Kategori::where('nama', $nama)->count()) {
            return $this->errorBadRequest("Nama kategori sudah ada -> {$nama}");
        }
        // safe to add. go ahead
        try {
            $k = new Kategori;
            $k->nama = $nama;
            $k->save();

            // log first
            AppLog::logInfo("Kategori '{$k->nama}' ditambahkan oleh {$r->userInfo['username']}", $k);

            return $this->respondWithArray([
                'id'    => $k->id,
                'uri'   => '/kategori/' . $k->id
            ]);
        } catch (\Exception $e) {
            return $this->errorBadRequest("Unknown error: " . $e->getMessage());
        }
    }

    // Pelabuhan
    public function getPelabuhan(Request $r) {
        $q = $r->get('q');

        $query = Pelabuhan::orderBy('id');

        if ($q) {
            $query->byKode($q)
                ->orWhere(function ($query) use ($q) {
                    $query->byNama($q);
                });
        }

        $paginator = $query->paginate($r->get('number'))
                            ->appends($r->except('page'));
        
        return $this->respondWithPagination($paginator, new PelabuhanTransformer);
    }

    public function getPelabuhanByKode(Request $r, $kode) {
        $pelabuhan = Pelabuhan::byKode($kode)->first();

        if (!$pelabuhan) {
            return $this->errorNotFound("Pelabuhan dengan kode {$kode} tidak ditemukan");
        }

        return $this->respondWithItem($pelabuhan, new PelabuhanTransformer);
    }

    // GET /kemasan
    public function getKemasan(Request $r) {
        $query = Kemasan::orderBy('kode');

        $q = $r->get('q');

        if ($q) {
            $query->byKode($q)
                ->orWhere(function ($query) use ($q) {
                    $query->byUraian($q);
                });
        }

        $paginator = $query->paginate($r->get('number'))
                            ->appends($r->except('page'));

        return $this->respondWithPagination($paginator, new KemasanTransformer);
    }

    // GET /kemasan/{kode}
    public function getKemasanByKode(Request $r, $kode) {
        $kemasan = Kemasan::byExactKode($kode)->first();

        if (!$kemasan) {
            return $this->errorNotFound("Jenis kemasan '{$kode}' tidak ditemukan");
        }

        return $this->respondWithItem($kemasan, new KemasanTransformer);
    }

    // GET /satuan
    public function getSatuan(Request $r) {
        $query = Satuan::orderBy('kode');

        $q = $r->get('q');

        if ($q) {
            $query->byKode($q)
                ->orWhere(function ($query) use ($q) {
                    $query->byUraian($q);
                });
        }

        $paginator = $query->paginate($r->get('number'))
                            ->appends($r->except('page'));

        return $this->respondWithPagination($paginator, new SatuanTransformer);
    }

    // GET /kemasan/{kode}
    public function getSatuanByKode(Request $r, $kode) {
        $satuan = Satuan::byExactKode($kode)->first();

        if (!$satuan) {
            return $this->errorNotFound("Jenis satuan '{$kode}' tidak ditemukan");
        }

        return $this->respondWithItem($satuan, new SatuanTransformer);
    }

    // GET /jenis-detail-sekunder
    public function getJenisDetailSekunder() {
        $data = ReferensiJenisDetailSekunder::all();

        return $this->respondWithCollection($data, new ReferensiJenisDetailSekunderTransformer);

        // return $this->respondWithArray($data);
    }

    // GET /airline
    public function getAllAirline() {
        $data = Airline::all();

        return $this->respondWithCollection($data, new AirlineTransformer);
    }

    // GET /airline/{kode}
    public function getAirlineByKode($kode) {
        $data = Airline::byExactCode($kode)->first();

        if (!$data) {
            return $this->errorNotFound("Airline with code {$kode} was not found.");
        }

        return $this->respondWithItem($data, new AirlineTransformer);
    }

    // GET /pemeriksa
    public function getPemeriksa(Request $r) {
        // first, grab a list of pemeriksa
        // then, depending on whether we only return the active one or not, 
        // return it

        try {
            // grab all user who has role pemeriksa
            $data = $this->sso->getUserByRole(['sibape.pemeriksa'], false);

            // modify it a bit
            $q = $r->get('q');

            if ($q && strlen(trim($q)) > 0) {
                // refine by name?
                $data['data'] = array_values(array_filter($data['data'], function ($e) use ($q) {
                    $pattern = "/$q/i";

                    return preg_match($pattern, $e['name']) || preg_match($pattern, $e['nip']);
                }));

                // remove key?
                
            }

            return $this->respondWithArray($data);
        } catch (\Exception $e) {
            return $this->errorBadRequest($e->getMessage() . ' Code: ' . $e->getCode());
        }
    }

    // GET /dokkap
    public function getJenisDokkap() {
        $jenisDokkap = ReferensiJenisDokkap::usable()->get();

        return $this->respondWithCollection($jenisDokkap, new ReferensiJenisDokkapTransformer);
    }

    // GET /jenis-pungutan
    public function getJenisPungutan() {
        $jenisPungutan = ReferensiJenisPungutan::all();

        return $this->respondWithCollection($jenisPungutan, new ReferensiJenisPungutanTransformer);
    }

    // GET /lokasi
    public function getLokasi() {
        return $this->respondWithCollection(Lokasi::all(), new LokasiTransformer);
    }

    // GET /tps
    public function getTps() {
        return $this->respondWithCollection(TPS::all(), new TPSTransformer);
    }

    // GET /pjt
    public function getPjt() {
        return $this->respondWithCollection(PJT::all(), new PJTTransformer);
    }

    // POST /pjt
    public function storePjt(Request $r) {
        DB::beginTransaction();
        try {
            // grab data,
            $nama = expectSomething($r->get('nama'), 'Nama PJT');
            $npwp = expectSomething($r->get('npwp'), 'NPWP PJT');
            $alamat = $r->get('alamat');

            // clear all sign
            $nama = trim(strtoupper($nama));
            $npwp = preg_replace('/[^\d]/i', "", $npwp);

            // validate
            if (strlen($nama) < '4') throw new \Exception("Nama PJT Terlalu pendek --> {$nama}");
            if (strlen($npwp) != 15) throw new \Exception("NPWP harus 15 digit --> {$npwp} = " . strlen($npwp) . " digit");

            // create new PJT
            $p = new PJT([
                'nama' => $nama,
                'npwp' => $npwp,
                'alamat' => $alamat
            ]);
            $p->save();

            // log it?
            AppLog::logInfo("PJT #{$p->id} was input by {$r->userInfo['username']}", $p, false);

            // commit
            DB::commit();

            // return
            return $this->respondWithArray([
                'id' => $p->id,
                'uri' => '/pjt/' . $p->id
            ]);
        } catch (PDOException $e) {
            DB::rollBack();
            return $this->errorBadRequest("PJT duplikat!");
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->errorBadRequest($e->getMessage());
        }
    }

    // GET /gudang
    public function getGudang() {
        return $this->respondWithCollection(Gudang::all(), new GudangTransformer);
    }

    // POST /tps/{id}/gudang
    public function storeGudang(Request $r, $tpsId) {
        DB::beginTransaction();
        try {
            // grab tps
            $t = TPS::findOrFail($tpsId);

            // grab data
            $kode = expectSomething($r->get('kode'), 'Kode Gudang');
            $kode = strtoupper(trim($kode));

            // kode gudang kudu 4 karakter
            if (strlen($kode) != 4) throw new \Exception("Kode gudang harus 4 karakter ==> {$kode}");

            // store
            $g = $t->gudang()->create([
                'kode' => $kode
            ]);

            // Log it
            AppLog::logInfo("Kode Gudang {$kode} ditambahkan oleh {$r->userInfo['username']}", $g, false);

            // commit
            DB::commit();

            // return new gudang info
            return $this->respondWithArray([
                'id' => (int) $g->id,
                'uri' => '/gudang/' . $g->id
            ]);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return $this->errorNotFound("TPS #{$tpsId} was not found.");
        } catch (PDOException $e) {
            DB::rollBack();
            return $this->errorBadRequest("Kode GUDANG DUPLIKAT");
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->errorBadRequest($e->getMessage());
        }
    }
}
