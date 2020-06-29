<?php

namespace App\Http\Controllers;

use App\DetailBarang;
use App\IHasGoods;
use App\ISpecifiable;
use App\Services\Instancer;
use App\Transformers\DetailBarangTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use League\Fractal\Manager;

class DetailBarangController extends ApiController
{
    // use instancer
    public function __construct(Manager $f, Request $r, Instancer $instancer)
    {
        parent::__construct($f, $r);
        $this->instancer = $instancer;
    }

    // index the penetapan of a dokumen?
    public function indexPenetapan(Request $r, $doctype, $docid) {
        try {
            //code...
            // instance here
            $d = $this->instancer->findOrFail($doctype, $docid);

            if (!($d instanceof ISpecifiable)) {
                throw new \Exception("Objek ". get_class($d) ." tidak bisa memiliki penetapan!");
            }

            $paginator = $d->penetapan()
                        ->paginate($r->get('number', 10))
                        ->appends($r->except('page'));

            return $this->respondWithPagination($paginator, new DetailBarangTransformer);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound($e->getMessage());
        } catch (\Exception $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }

    // index the penetapan of a dokumen?
    public function indexDetailBarang(Request $r, $doctype, $docid) {
        try {
            //code...
            // instance here
            $d = $this->instancer->findOrFail($doctype, $docid);

            if (!($d instanceof IHasGoods)) {
                throw new \Exception("Objek ". get_class($d) ." tidak bisa memiliki detail barang!");
            }

            $paginator = $d->detailBarang()
                        ->paginate($r->get('number', 10))
                        ->appends($r->except('page'));

            return $this->respondWithPagination($paginator, new DetailBarangTransformer);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound($e->getMessage());
        } catch (\Exception $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }

    // show one instance of penetapan
    public function showPenetapan(Request $r, $id) {
        try {
            $d = DetailBarang::isPenetapan()->findOrFail($id);

            return $this->respondWithItem($d, new DetailBarangTransformer);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound("Detail Barang #{$id} was not found");
        } catch (\Exception $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }

    // show one instance of detailBarang
    public function showDetailBarang(Request $r, $id) {
        try {
            $d = DetailBarang::isDetailBarang()->findOrFail($id);

            return $this->respondWithItem($d, new DetailBarangTransformer);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound("Detail Barang #{$id} was not found");
        } catch (\Exception $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }

    // store penetapan
    public function storePenetapan(Request $r, $doctype, $docid) {
        try {
            // grab parents?
            $header = $this->instancer->findOrFail($doctype, $docid);


        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound("endpoint '{$doctype}/{$docid}' was not valid");
        } catch (\Exception $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }
}
