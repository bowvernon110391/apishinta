<?php

namespace App\Http\Controllers;

use App\Negara;
use Illuminate\Http\Request;
use App\Transformers\NegaraTransformer;

class ReferensiController extends ApiController
{
    // GET /negara
    public function getAllNegara(Request $r) {
        $negara = App\Negara::all();
        return $this->respondWithCollection($negara, new NegaraTransformer);
    }

    // GET /negara/{kode}
    public function getNegaraByCode($kode) {
        $negara = App\Negara::byExactCode($kode)->first();

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
}
