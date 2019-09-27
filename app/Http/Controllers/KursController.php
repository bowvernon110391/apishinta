<?php

namespace App\Http\Controllers;

use App\Transformers\KursTransformer;
use Illuminate\Http\Request;
use App\Kurs;
use ErrorException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use InvalidArgumentException;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;

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
