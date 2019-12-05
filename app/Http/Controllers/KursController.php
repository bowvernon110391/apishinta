<?php
namespace App\Http\Controllers;

use App\Transformers\KursTransformer;
use Illuminate\Http\Request;
use App\Kurs;
use App\AppLog;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class KursController extends ApiController
{
    // return single kurs data based on id
    public function show($id) {
        $kurs = Kurs::find($id);

        if (!$kurs) {
            return $this->errorNotFound("Gak nemu data kurs dengan id {$id}");
        }

        return $this->respondWithItem($kurs, new KursTransformer);
    }

    // create new kurs data
    public function store(Request $request) {
        
        // only accept json request
        if (!$request->isJson()) {
            return $this->errorBadRequest("Cannot accept your request. Read the spec bitch!");
        }

        try {
            // validate all data
            // 1st, data must be complete
            if (!$request->kode_valas 
                || !$request->kurs_idr 
                || !$request->jenis
                || !$request->tanggal_awal) {
                throw new BadRequestHttpException("Data either invalid or incomplete or both. Explain yerself!");
            }
            
            // create kurs
            $kurs = new Kurs;
            $kurs->kode_valas   = $request->kode_valas;
            $kurs->kurs_idr = (float) $request->kurs_idr;
            $kurs->jenis    = $request->jenis;
            $kurs->tanggal_awal = $request->tanggal_awal;
            // tanggal akhir is optional
            if ($request->tanggal_akhir) {
                $kurs->tanggal_akhir= $request->tanggal_akhir;
            }

            // attempt to save
            if (!$kurs->save()) {
                return $this->errorBadRequest();
            }

            // log some shit
            $kurs->logs()->save(AppLog::logInfo("Created kurs {$kurs->id} by {$request->user['nama']}"));

            // well, saved. return its id
            return $this->respondWithArray([
                'id'    => $kurs->id,
                'uri'   => '/kurs/' . $kurs->id
            ]);
        } catch (QueryException $e) {
            // or the query 
            if ($e->getCode() != 2002) {
                // it's user's fault!
                return $this->errorBadRequest("Fuck you! Your data is bad!");
            }
            return $this->errorInternalServer();
        } catch (\PDOException $e) {
            // This would be server's down
            return $this->errorInternalServer();
        } catch (BadRequestHttpException $e) {
            // user supply invalid data
            return $this->errorBadRequest($e->getMessage());
        } catch (\Exception $e) {
            return $this->errorServiceUnavailable($e->getMessage());
        }

        
    }

    // update kurs data
    // format {'id': x, 'kode_kurs': xxx, 'kurs_idr': xxx, 'tanggal_awal': xxx, 'tanggal_akhir': xxx}
    public function update(Request $request, $id) {

        // json kah?
        if (!$request->isJson()) {
            return $this->errorBadRequest();
        }

        // dapet kah?
        $kurs = Kurs::find($id);

        if (!$kurs) {
            return $this->errorNotFound("Kurs dengan id {$id} tidak ditemukan");
        }

        // ok, coba ganti
        try {
            $kurs->kode_valas   = $request->kode_valas;
            $kurs->kurs_idr     = (float) $request->kurs_idr;
            $kurs->jenis        = $request->jenis;
            $kurs->tanggal_awal = $request->tanggal_awal;
            $kurs->tanggal_akhir= $request->tanggal_akhir;

            $kurs->save();



            // sukses, return 204 tanpa response body
            return $this->setStatusCode(204)
                ->respondWithEmptyBody();
        } catch (\Exception $e) {
            return $this->errorBadRequest("Request ditolak. Cek lagi data inputan anda");
        }
    }

    // return all kurs, possibly with some query involved
    public function index(Request $request) {
        // parse url for embedded fractal data
        // ONLY NEEDED IF THERE'S A POSSIBLE INCLUDES (EMBED/SIDE LOADING)
        // $this->fractal->parseIncludes($request->get('include', ''));

        // parse query string for our custom query?
        // $qKode_valas = $request->get('kode', '');               // param: kode
        $qTanggal  = $request->get('tanggal');                  // param: tanggal, if not supplied default to current date
        // $qJenis = $request->get('jenis');                       // param: jenis
        $qFrom = $request->get('from');
        $qTo = $request->get('to');
        $qWild = $request->get('q');
        $qId = $request->get('id');

        // set default tanggal if from and to is invalid
        if (!$qFrom && !$qTo && !$qTanggal) {
            $qTanggal = date('Y-m-d');
        }

        // build query (use try-catch)
        $query = Kurs::when($qWild, function ($query) use ($qWild) {
            $query->kode($qWild)
                ->orWhere(function ($q) use ($qWild) {
                    $q->kode($qWild);
                })
                ->orWhere(function ($q) use ($qWild) {
                    $q->jenis($qWild);
                });
        })
        ->when($qId, function ($query) use ($qId) {
            $query->whereIn('id', explode(',', $qId));
        })
        ->when($qTanggal, function ($query) use ($qTanggal) {
            $query->perTanggal(sqlDate($qTanggal));
        })
        ->when($qFrom && $qTo, function ($query) use ($qFrom, $qTo) {
            $query->periode(sqlDate($qFrom), sqlDate($qTo));
        });
       
        
        // order based on kode_kurs then jenis kurs
        $query->orderBy('kode_valas', 'asc')
            ->orderBy('jenis', 'asc');

        $paginator = $query
                    ->paginate($request->input('number'))
                    ->appends($request->except('page'));
                    // ->appends($request->except('sso_user'));

        // return $this->respondWithCollection($kurs, new KursTransformer);
        return $this->respondWithPagination($paginator, new KursTransformer);
    }

    // return valid kurs on that date
    public function showValidKursOnDate(Request $request, $date) {
        // convert date
        $date = sqlDate($date);
        try {
            $result = Kurs::findValidKursOnDateOrFail($date);
        } catch (InvalidArgumentException $e) {
            // user is an idiot, tell him so
            return $this->errorBadRequest($e->getMessage());
        } catch (ModelNotFoundException $e) {
            // no data found, up to you though either
            // to respond with empty response or error
            return $this->errorNotFound($e->getMessage());
        }

        // if things go smooth we go here
        return $this->respondWithCollection($result, new KursTransformer);
    }

    // delete stuffs
    public function destroy(Request $request, $id) {
        // try to output user info instead
        $userInfo = $request->userInfo;

        if ($userInfo) {

            // if console tried to delete, allow it
            if (userHasRole('CONSOLE', $userInfo)) {
                $k = Kurs::find($id);
                if (!$k) {
                    return $this->errorNotFound("Kurs #{$id} tidak ditemukan");
                }

                // delet dis
                try {
                    $k->delete();

                    // empty response
                    return $this->setStatusCode(204)
                                ->respondWithEmptyBody();
                } catch (\Exception $e) {
                    return $this->errorBadRequest($e->getMessage());
                }
            }           
        }
        // no console auth. tell him to upgrade his account maybe?
        return $this->errorForbidden("You may not do that. Not an admin");
    }

    // pull from BKF
    public function pullFromBKF(Request $r) {
        $kursData = grabKursData();

        $inserted = 0;
        $updated = 0;

        if ($kursData) {
            DB::beginTransaction();

            try {
                // save for each kurs
                foreach ($kursData['data'] as $valas => $nilaiKurs) {
                    // try to search first
                    $k = Kurs::where('kode_valas', $valas)
                                ->where('jenis', 'KURS_PAJAK')
                                ->where('tanggal_awal', $kursData['dateStart'])
                                ->where('tanggal_akhir', $kursData['dateEnd'])
                                ->get();

                    // welp, duplicate found. let's replace it
                    if (count($k)) {
                        // replace every occurence (get returns collection)
                        foreach ($k as $kRep) {
                            $kRep->kurs_idr = $nilaiKurs;
                            $kRep->save();

                            $updated ++;
                        }
                        // $updated++;
                    } else {
                        // new shiet. save it
                        $k = new Kurs();
                        $k->kode_valas = $valas;
                        $k->jenis = 'KURS_PAJAK';
                        $k->tanggal_awal = trim($kursData['dateStart']);
                        $k->tanggal_akhir = trim($kursData['dateEnd']);
                        $k->kurs_idr = $nilaiKurs;

                        

                        // throw new \Exception(json_encode($k));

                        $k->save();

                        $inserted ++;
                    }
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();

                return $this->errorInternalServer($e->getMessage());
            }

            // anggep berhasil
            return $this->respondWithArray([
                'kurs_updated'  => date('Y-m-d H:i:s'),
                'inserted'  => $inserted,
                'updated'   => $updated,
                'info' => $kursData['data']
            ]);
        } else {
            // failed to grab data...for whatever reason
            // flag server error
            return $this->errorServiceUnavailable("Situs BKF lagi down kayaknya");
        }
    }
}
