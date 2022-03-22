<?php

namespace App\Http\Controllers;

use App\DetailBarang;
use App\IHasGoods;
use App\ISpecifiable;
use App\Penetapan;
use App\PIBK;
use App\Services\Instancer;
use App\SSOUserCache;
use App\Transformers\DetailBarangTransformer;
use App\Transformers\PenetapanTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use League\Fractal\Manager;

class DetailBarangController extends ApiController
{
    // use instancer
    public function __construct(Manager $f, Request $r, Instancer $instancer)
    {
        parent::__construct($f, $r);
        $this->instancer = $instancer;
    }

    // index penetapan (barang yg udh dpt penetapan)
    public function index(Request $r) {
        // ?doc=cd,pibk
        $doctype = explode(',', $r->input('doctype', 'cd'));
        // resolve it + filter
        $doctype =
        array_filter(
            array_map(function($e){
                return $this->instancer->resolveClassname($e);
            }, $doctype)
            ,
            function($e){
                return !is_null($e);
            }
        );

        $q = $r->input('q');
        $from = $r->input('from');
        $to = $r->input('to');
        $cat = $r->input('category', []);

        // eager load header.ndpbm
        $query = DetailBarang::with(['header.ndpbm'])
                ->sudahPenetapan($doctype)
                ->when($q, function ($q1) use ($q) {
                    $q1->where('uraian', 'like', "%$q%");
                })
                ->when($from, function($qfrom) use ($from) {
                    $qfrom->where('created_at', '>=', $from);
                })
                ->when($to, function($qto) use ($to) {
                    $qto->where('created_at', '<=', $to);
                })
                ->when($cat, function($qcat) use ($cat) {
                    $qcat->byKategori($cat);
                })
                // ->latest();
                ->orderBy('updated_at', 'DESC');

        $paginator = $query
                    ->paginate($r->input('number', 10))
                    ->appends($r->except('page'));
        return $this->respondWithPagination($paginator, new PenetapanTransformer);
    }

    // index the penetapan of a dokumen?
    public function indexPenetapan(Request $r, $doctype, $docid) {
        try {
            //code...
            // instance here
            $d = $this->instancer->findOrFail($doctype, $docid);

            if (!($d instanceof IHasGoods)) {
                throw new \Exception("Objek ". get_class($d) ." tidak bisa memiliki penetapan!");
            }

            $paginator = $d->detailBarang()->isPenetapan()
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

            $paginator = $d->detailBarang()->isPengajuan()
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
            $d = DetailBarang::isPengajuan()->findOrFail($id);

            return $this->respondWithItem($d, new DetailBarangTransformer);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound("Detail Barang #{$id} was not found");
        } catch (\Exception $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }

    // store penetapan
    public function storePenetapan(Request $r, $doctype, $docid) {
        DB::beginTransaction();
        try {
            // grab parents?
            $header = $this->instancer->findOrFail($doctype, $docid);

            // is it locked?
            if ($header->is_locked) {
                throw new \Exception("Dokumen /{$doctype}/{$docid} sudah terkunci!");
            }

            // it must be of instance ISpecifiable
            if (!($header instanceof IHasGoods)) {
                throw new \Exception("header tidak bisa menyimpan data penetapan barang (IHasGoods)");
            }

            // it's not locked, so let's store it
            $d = new DetailBarang();
            // sync primary data
            $d->syncPrimaryData($r);
            // save it before we can move further
            $header->detailBarang()->save($d);
            // sync secondary data
            $d->syncSecondaryData($r);

            // sync tarif
            $d->syncTarif($r->get('tarif')['data']);

            // gotta spawn penetapan entry too
            $p = new Penetapan();
            $p->penetapan()->associate($d);
            $p->pejabat()->associate(SSOUserCache::byId($r->userInfo['user_id']));
            $p->save();

            // trigger handler
            $header->onCreateItem($d);

            DB::commit();

            return $this->respondWithArray([
                'id' => $d->id,
                'uri' => '/penetapan/' . $d->id
            ]);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return $this->errorNotFound($e->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorBadRequest($e->getMessage());
        }
    }

    // update penetapan
    public function updatePenetapan(Request $r, $id) {
        DB::beginTransaction();

        try {
            $d = DetailBarang::findOrFail($id);

            if (!$d->is_penetapan) {
                throw new \Exception("Data ini bukan data penetapan!");
            }

            if (!$d->pivotPenetapan) {
                throw new \Exception("Data ini belum ditetapkan!");
            }

            // sync primary and secondary data
            $d->syncPrimaryData($r);
            $d->save();
            $d->syncSecondaryData($r);

            // sync tarif
            $d->syncTarif($r->get('tarif')['data']);

            // gotta update penetapan too!
            $p = $d->pivotPenetapan;
            $p->pejabat()->associate(SSOUserCache::byId($r->userInfo['user_id']));
            $p->save();

            // trigger listener
            $header = $d->header;
            if ($header) {
                // is header instance of IHasGoods?
                if (!($header instanceof IHasGoods)) {
                    throw new \Exception("Header dari penetapan #{$d->id} bukan container untuk data barang!");
                }

                // save, trigger it
                $header->onUpdateItem($d);
            }

            DB::commit();

            return $this->setStatusCode(204)
                        ->respondWithEmptyBody();
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return $this->errorNotFound($e->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorBadRequest($e->getMessage());
        }
    }

    // delete penetapan
    public function deletePenetapan(Request $r, $id) {
        DB::beginTransaction();

        try {
            $d = DetailBarang::findOrFail($id);

            if (!$d->is_penetapan) {
                throw new \Exception("Data ini bukan data penetapan!");
            }

            if (!$d->pivotPenetapan) {
                throw new \Exception("Data ini belum ditetapkan!");
            }

            // trigger listener
            $header = $d->header;
            if ($header) {
                // is header instance of IHasGoods?
                if (!($header instanceof IHasGoods)) {
                    throw new \Exception("Header dari penetapan #{$d->id} bukan container untuk data barang!");
                }

                // if it's locked, gotta tell em
                if (!canEdit($header->is_locked, $r->userInfo)) {
                    throw new \Exception("Header is locked already! cannot delete!");
                }

                // save, trigger it
                $header->onDeleteItem($d);
            }

            // finally delete it
            $d->pivotPenetapan()->delete();
            $d->delete();

            DB::commit();

            return $this->setStatusCode(204)
                        ->respondWithEmptyBody();
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return $this->errorNotFound($e->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorBadRequest($e->getMessage());
        }
    }

}
