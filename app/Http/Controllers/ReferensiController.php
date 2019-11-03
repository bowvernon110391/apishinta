<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Negara;
use App\HsCode;
use App\Kategori;
use App\Transformers\HsCodeTransformer;
use App\Transformers\NegaraTransformer;
use App\Transformers\KategoriTransformer;

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
}
