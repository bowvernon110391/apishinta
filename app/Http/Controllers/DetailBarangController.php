<?php

namespace App\Http\Controllers;

use App\DetailBarang;
use App\IHasGoods;
use App\ISpecifiable;
use App\Penetapan;
use App\Services\Instancer;
use App\SSOUserCache;
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

            // is it locked?
            if ($header->is_locked) {
                throw new \Exception("Dokumen /{$doctype}/{$docid} sudah terkunci!");
            }

            // it must be of instance ISpecifiable
            if (!($header instanceof ISpecifiable)) {
                throw new \Exception("header tidak bisa menyimpan data penetapan (ISpecifiable)");
            }

            // it's not locked, so let's store it
            $d = new DetailBarang();
            // sync primary data
            $d->syncPrimaryData($r);
            // save it before we can move further
            $header->penetapan()->save($d);
            // sync secondary data
            $d->syncSecondaryData($r);

            // gotta spawn penetapan entry too
            $p = new Penetapan();
            $p->data()->associate($d);
            $p->pejabat()->associate(SSOUserCache::byId($r->userInfo['user_id']));
            $p->save();
            
            return $this->respondWithArray([
                'id' => $d->id,
                'uri' => '/penetapan/' . $d->id
            ]);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound($e->getMessage());
        } catch (\Exception $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }

    // update penetapan
    public function updatePenetapan(Request $r, $id) {
        try {
            $d = DetailBarang::findOrFail($id);

            if (!$d->is_penetapan) {
                throw new \Exception("Data ini bukan data penetapan!");
            }

            if (!$d->penetapanHeader) {
                throw new \Exception("Data ini belum ditetapkan!");
            }

            // sync primary and secondary data
            $d->syncPrimaryData($r);
            $d->save();
            $d->syncSecondaryData($r);

            // gotta update penetapan too!
            $p = $d->penetapanHeader;
            $p->pejabat()->associate(SSOUserCache::byId($r->userInfo['user_id']));
            $p->save();

            return $this->setStatusCode(204)
                        ->respondWithEmptyBody();
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound($e->getMessage());
        } catch (\Exception $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }
}
