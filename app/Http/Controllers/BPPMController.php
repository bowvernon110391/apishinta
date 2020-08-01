<?php

namespace App\Http\Controllers;

use App\BPPM;
use App\Transformers\BPPMTransformer;
use Illuminate\Http\Request;

class BPPMController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $r)
    {
        // grab some parameter
        $q = $r->get('q') ?? '';
        $from = $r->get('from');
        $to = $r->get('to');
        $deep = ($r->get('deep') == 'true');

        $billingStatus = $r->get('billing-status');
        // list all bppm, latest first
        $query = BPPM::query()
                ->when($q || $from || $to && !$deep, function ($q1) use ($q, $from, $to) {
                    $q1->byQuery($q, $from, $to);
                })
                ->when($deep, function ($q1) use ($q, $from, $to) {
                    $q1->orWhere(function ($q2) use ($q, $from, $to) {
                        $q2->deepQuery($q, $from, $to);
                    });
                })
                ->when($billingStatus, function ($q1) use ($billingStatus) {
                    $q1->whereHasBilling($billingStatus == 'true');
                })
                ->latest()
                ->orderBy('id','desc');
        // respond with pagination
        $paginator = $query->paginate($r->get('number', 10))
                            ->appends($r->except('page'));
        return $this->respondWithPagination($paginator, new BPPMTransformer);
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
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
