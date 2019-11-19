<?php

namespace App\Http\Controllers;

use App\AppLog;
use Illuminate\Http\Request;
use App\Negara;
use App\HsCode;
use App\Kategori;
use App\Kemasan;
use App\Pelabuhan;
use App\Satuan;
use App\Transformers\HsCodeTransformer;
use App\Transformers\NegaraTransformer;
use App\Transformers\KategoriTransformer;
use App\Transformers\KemasanTransformer;
use App\Transformers\PelabuhanTransformer;
use App\Transformers\SatuanTransformer;

class ReferensiController extends ApiController
{
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

            $id = $r->get('id');
            $kode = $r->get('kode');
            $uraian = $r->get('uraian');

            if (!$id) {
                throw new \Exception("id negara tidak valid -> ".$r->get('id'));
            }
            if (!$kode) {
                throw new \Exception("kode negara tidak valid -> " . $kode);
            }
            if (!$uraian) {
                throw new \Exception("uraian negara tidak valid -> ".$uraian);
            }
            // try to save
            $n = new Negara;
            $n->id = $id;
            $n->kode = $kode;
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
}
