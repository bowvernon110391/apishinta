<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Penumpang;
use App\Transformers\PenumpangTransformer;
use Illuminate\Database\QueryException;

class PenumpangController extends ApiController
{
    /**
     * Display a listing of penumpang. possibly with query
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // parameter yg ada {q, nama, kebangsaan, pekerjaan, nopaspor}
        // q: apa aja, bakal nyari dari nama, kebangsaan, nopaspor. klo pake q, yg lain jd gk valid
        if ( ($q=$request->get('q')) ) {
            // wildcard search, gunakan apa yg ada
            $qSearch = "%{$q}%";
            $queryPenumpang = Penumpang::where("nama", "LIKE", $qSearch)
                                        ->orWhere("no_paspor", "LIKE", $qSearch)
                                        ->orWhere("pekerjaan", "LIKE", $qSearch)
                                        ->orWhere(function ($query) use ($q) {
                                            $query->byNegara($q);
                                        });
        } else {
            // pake parameter lain
            $qNama = $request->get('nama');
            $qKebangsaan = $request->get('negara') ?? $request->get('kebangsaan'); // negara/kebangsaan dianggap sama
            $qNoPaspor = $request->get('nopaspor');
            $qPekerjaan = $request->get('pekerjaan');

            // optimization: if any of the parameter exists, use it
            if ($qNama || $qKebangsaan || $qNoPaspor || $qPekerjaan) {
                // specific query using AND
                $queryPenumpang = Penumpang::when($qNama, function ($query) use ($qNama) {
                    // pake nama penumpang
                    $query->where("nama", "LIKE", "%{$qNama}%");
                })->when($qKebangsaan, function ($query) use ($qKebangsaan) {
                    // pake nama kebangsaan
                    // $query->where("kebangsaan", "LIKE", "%{$qKebangsaan}%");
                    $query->byNegara($qKebangsaan);
                })->when($qNoPaspor, function ($query) use ($qNoPaspor) {
                    // pake nomor paspor
                    $query->where("no_paspor", "LIKE", "%{$qNoPaspor}%");
                })->when($qPekerjaan, function ($query) use ($qPekerjaan) {
                    // pake pekerjaan
                    $query->where("pekerjaan", "LIKE", "%{$qPekerjaan}%");
                });
            } else {
                // return all
                $queryPenumpang = Penumpang::whereRaw("1");
            }
        }   

        // pake buat pagination
        $paginator = $queryPenumpang
                ->paginate($request->get('number'))
                ->appends($request->except('page'));

        // respon dengan paginasi
        return $this->respondWithPagination($paginator, new PenumpangTransformer);
    }

    /**
     * Store data penumpang baru
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
        // check if it's json
        if (!$request->isJson())
            return $this->errorBadRequest("Sori, cuman nerima data dalam format JSON");

        // grab the parameter?
        $nama = $request->get('nama');
        $tgl_lahir  = sqlDate($request->get('tgl_lahir'));
        $kebangsaan = $request->get('kebangsaan');
        $no_paspor  = $request->get('no_paspor');
        $pekerjaan  = $request->get('pekerjaan');

        // one check after another
        if (!$nama) {
            return $this->errorBadRequest('Nama penumpang tidak sesuai standar => ' . $nama);
        }
        if (!$tgl_lahir) {
            return $this->errorBadRequest('Tanggal lahir invalid => ' . $tgl_lahir);
        }
        if (!$kebangsaan) {
            return $this->errorBadRequest('Kebangsaan invalid => ' . $kebangsaan);
        }
        if (!$no_paspor) {
            return $this->errorBadRequest('no paspor tidak valid => ' . $no_paspor);
        }
        if (!$pekerjaan) {
            return $this->errorBadRequest('Pekerjaan tidak valid => ' . $pekerjaan);
        }

        // attempt to store it
        // use try catch
        try {
            $penumpang = new Penumpang;

            $penumpang->nama = $nama;
            $penumpang->tgl_lahir   = $tgl_lahir;
            $penumpang->no_paspor   = $no_paspor;
            $penumpang->kebangsaan  = $kebangsaan;
            $penumpang->pekerjaan   = $pekerjaan;

            // coba save
            if (!$penumpang->save())
                throw new \Exception("Gagal menyimpan data penumpang");
            
            // berhasil? kembaliin data berupa id dan uri
            return $this->respondWithArray([
                'id'    => $penumpang->id,
                'uri'   => '/penumpang/' . $penumpang->id
            ]);
        } catch (QueryException $e) {
            // klo kode 2002 brarti db down
            if ($e->getCode() == 2002) {
                return $this->errorInternalServer();
            }
            // ada data yg gk bisa diinput ke db. mgkn krn default value gk diset?
            return $this->errorBadRequest("Cek kembali data inputannya ada yg gk sesuai");
        } catch (\Exception $e) {
            // last straw. error apaan yak? kasitau aja dah bodo amat
            return $this->errorServiceUnavailable($e->getMessage());
        }
    }

    /**
     * Display single penumpang brdsrkn id
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // grab single id
        $penumpang = Penumpang::find($id);

        if (!$penumpang) {
            // gk ketemu, 404
            return $this->errorNotFound("Penumpang dengan id {$id} tidak ditemukan");
        }

        // ketemu, respon dh
        return $this->respondWithItem($penumpang, new PenumpangTransformer);
    }

    /**
     * Update data penumpang brdsrkn id, bikin baru klo blm ada
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        
        // check if it's json
        if (!$request->isJson())
            return $this->errorBadRequest("Sori, cuman nerima data dalam format JSON");

        // grab the parameter?
        $nama = $request->get('nama');
        $tgl_lahir  = sqlDate($request->get('tgl_lahir'));
        $kebangsaan = $request->get('kebangsaan');
        $no_paspor  = $request->get('no_paspor');
        $pekerjaan  = $request->get('pekerjaan');

        // check if all parameter exists
        if (!$nama || !$tgl_lahir || !$kebangsaan || !$no_paspor || !$pekerjaan) {
            // shiet, one of em is missing. return error
            return $this->errorBadRequest("Data penumpang tidak lengkap. Silahkan lengkapi");
        }

        // attempt to store it
        // use try catch
        try {
            // ambil data penumpang, bkin baru klo blm ada
            $penumpang = Penumpang::findOrNew($id);

            $penumpang->nama = $nama;
            $penumpang->tgl_lahir   = $tgl_lahir;
            $penumpang->no_paspor   = $no_paspor;
            $penumpang->kebangsaan  = $kebangsaan;
            $penumpang->pekerjaan   = $pekerjaan;

            // coba save
            if (!$penumpang->save())
                throw new $this->errorBadRequest();
            
            // berhasil? Utk put gk ngembaliin data, ckup kembaliin respon 204
            $this->setStatusCode(204)
                ->respondWithEmptyBody();
        } catch (QueryException $e) {
            // klo kode 2002 brarti db down
            if ($e->getCode() == 2002) {
                return $this->errorInternalServer();
            }
            // ada data yg gk bisa diinput ke db. mgkn krn default value gk diset?
            return $this->errorBadRequest("Cek kembali data inputannya ada yg gk sesuai");
        } catch (\Exception $e) {
            // last straw. error apaan yak? kasitau aja dah bodo amat
            return $this->errorServiceUnavailable($e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // DELETE PENUMPANG. Should it be implemented? No, I guess
    }
}
