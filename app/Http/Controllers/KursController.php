<?php

namespace App\Http\Controllers;

use App\Transformers\KursTransformer;
use Illuminate\Http\Request;
use App\Kurs;
use ErrorException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use InvalidArgumentException;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
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
        // only someone registered may update kurs
        // check for bearer token presence? and then validate
        if (!$request->bearerToken()) {
            return $this->errorUnauthorized();
        }

        // maybe token supplied but not valid
        $userInfo = getUserInfo($request->bearerToken());

        if (!$userInfo) {
            // no token or token exists but it's invalid
            return $this->errorUnauthorized("Your token is not valid d00d");
        }
        
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

            // well, saved. return its id
            return $this->respondWithArray([
                'kurs_id'   => $kurs->id
            ]);
        } catch (QueryException $e) {
            // or the query 
            if ($e->getCode() != 2002) {
                // it's user's fault!
                return $this->errorBadRequest("Fuck you! Your data is bad!");
            }
            return $this->errorInternalServer();
        } catch (PDOException $e) {
            // This would be server's down
            return $this->errorInternalServer();
        } catch (BadRequestHttpException $e) {
            // user supply invalid data
            return $this->errorBadRequest($e->getMessage());
        } catch (\Exception $e) {
            return $this->errorServiceUnavailable();
        }

        
    }

    // return all
    public function index(Request $request) {
        // parse url for embedded fractal data
        $this->fractal->parseIncludes($request->get('include', ''));

        // parse query string for our custom query?
        $qKode_valas = $request->get('kode', '');
        $qTanggal  = $request->get('tanggal', date('Y-m-d'));
        $qJenis = $request->get('jenis');

        // build query (use try-catch)
        $query = Kurs::where('kode_valas', 'LIKE', '%'.$qKode_valas.'%')    // kode_valas LIKE %%
                ->where('tanggal_awal', '<=', $qTanggal)                    // $qTanggal BETWEEN tanggal_awal
                ->where('tanggal_akhir', '>=', $qTanggal)                   //      AND tanggal_akhir
                ->when($qJenis, function($query) use ($qJenis) {            // optional where:
                    $query->where('jenis', '=', $qJenis);                   //      WHERE jenis = xxx
                });
       
        
        // order based on kode_kurs then jenis kurs
        $query->orderBy('kode_valas', 'asc')
            ->orderBy('jenis', 'asc');

        $paginator = $query->paginate($request->input('number', 10))
                    ->appends($request->except('page'));

        // return $this->respondWithCollection($kurs, new KursTransformer);
        return $this->respondWithPagination($paginator, new KursTransformer);
    }

    // return valid kurs on that date
    public function showValidKursOnDate(Request $request, $date) {
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
}
