<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\CD;
use App\Transformers\CDTransformer;
use App\Transformers\DetailCDTransformer;

class CDController extends ApiController
{
    /**
     * Display a listing of Customs Declaration, possibly with query strings for custom query
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $query = '';
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display Customs Declaration Header Data 
     * Jumlah detail gk dimunculin krn kemungkinan besar dan butuh paginasi
     * 
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $this->fractal->parseIncludes($request->get('include', ''));

        $cd = CD::find($id);

        if (!$cd) {
            return $this->errorNotFound("Gak nemu data CD dengan id {$id}");
        }

        return $this->respondWithItem($cd, new CDTransformer);
    }

    /**
     * Display the details of a Customs Declaration
     * with pagination
     */
    public function showDetails(Request $request, $id) {
        $cd = CD::find($id);

        if (!$cd) {
            return $this->errorNotFound("Gak nemu data CD dengan id {$id}");
        }

        // mungkin kah gk ada CD detailsnya?
        $paginator = $cd->details()
                    ->paginate($request->get('number', 10))
                    ->appends($request->except('page'));

        return $this->respondWithPagination($paginator, new DetailCDTransformer);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
