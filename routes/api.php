<?php
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
/* 
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
}); */

// CORS allowed-methods. Biar user gk coba macem2
$corsGroup = [
    'readOnly'  => 'cors:GET,OPTIONS',  // item yg read only cuman bsa GET sama OPTIONS
    'singleItem'=> 'cors:GET,PUT,DELETE,OPTIONS,PATCH', // single item bsa macem2
    'all'       => 'cors:*',    // klo bisa jgn pake ini ya
    'resourceGroup'  => 'cors:GET,POST,OPTIONS' // group bisa diinsert, dilihat, dicek
];

// Kayaknya bagusnya digroup per endpoints dah
// OPTIONS /* untuk menghandle preflight CORS request
Route::options('/{fuckers}', 'ApiController@options')
        ->where('fuckers', '.+')
        ->middleware('cors:GET,POST,PUT,DELETE,OPTIONS,PATCH,HEAD');


//====================================================================================================
// ENDPOINTS Kurs
//====================================================================================================
// GET /kurs/2019-09-02 => ambil data kurs yg valid per tanggal tsb
Route::get('/kurs/{tanggal}', 'KursController@showValidKursOnDate')
        ->where('tanggal', '^[\d-]{4,}$')
        ->middleware($corsGroup['readOnly']);

// GET /kurs    => ambil data kurs (collection), bisa handle query
Route::get('/kurs', 'KursController@index')
        ->middleware($corsGroup['resourceGroup']);

// GET /kurs/2  => ambil data kurs brdsrkn id
Route::get('/kurs/{id}', 'KursController@show')
        ->middleware($corsGroup['singleItem']);

// POST /kurs   => tambah data kurs {kode_valas, kurs_idr, jenis, tanggal_awal, tanggal_akhir}
Route::post('/kurs', 'KursController@store')
        ->middleware($corsGroup['resourceGroup'], 'role:PDTT,CONSOLE');

// PUT /kurs/{id}       => update/replace data kurs id {id} dengan format {kode_valas, kurs_idr, jenis, tanggal_awal, tanggal_akhir}
Route::put('/kurs/{id}', 'KursController@update')
        ->middleware($corsGroup['singleItem'], 'role:PDTT,CONSOLE');

// DELETE /kurs/{id}    => delete data kurs id {id}
Route::delete('/kurs/{id}', 'KursController@destroy')
        ->middleware($corsGroup['singleItem'], 'role:CONSOLE,PDTT');

//====================================================================================================
// ENDPOINTS PENUMPANG
// Data penumpang adalah data rahasia, jd kasih guard di endpointsnya
// asal tokennya dari user yg valid dan aktif, gk masalah
//====================================================================================================
        
// GET /penumpang => list data penumpang dgn paginasi + query
Route::get('/penumpang', 'PenumpangController@index')
        ->middleware($corsGroup['resourceGroup'], 'role');

// GET /penumpang/3     => tampilkan data penumpang dgn id 3
Route::get('/penumpang/{id}', 'PenumpangController@show')
        ->middleware($corsGroup['singleItem'], 'role');

// POST /penumpang      => insert data penumpang
Route::post('/penumpang', 'PenumpangController@store')
        ->middleware($corsGroup['resourceGroup'], 'role');

// PUT /penumpang/3     => update/insert data penumpang dgn id 3
Route::put('/penumpang/{id}', 'PenumpangController@update')
        ->middleware($corsGroup['singleItem'], 'role');

//====================================================================================================
// ENDPOINTS CD
// CD itu classified, jd kasih guard di api endpointsnya
//====================================================================================================
// CD subresource dari dokumens
Route::get('/dokumen/cd', 'CDController@index')
        ->middleware($corsGroup['resourceGroup'], 'role');

// GET /dokumen/cd/2  => ambil data cd + relasinya
Route::get('/dokumen/cd/{id}', 'CDController@show')
        ->middleware($corsGroup['singleItem'], 'role');

// GET /dokumen/cd/2/details    => ambil data detail cd
Route::get('/dokumen/cd/{id}/details', 'CDController@showDetails')
        ->middleware($corsGroup['resourceGroup'], 'role');

//====================================================================================================
// ENDPOINTS untuk data referensi umum (negara, satuan, kemasan, hs)
//====================================================================================================
// GET /negara
Route::get('/negara', 'ReferensiController@getAllNegara')
        ->middleware($corsGroup['resourceGroup']);

// GET /negara/id
Route::get('/negara/{kode}', 'ReferensiController@getNegaraByCode')
        ->middleware($corsGroup['singleItem']);

// POST /negara
Route::post('/negara', 'ReferensiController@storeNegara')
        ->middleware($corsGroup['resourceGroup']);

// GET /hs
Route::get('/hs', 'ReferensiController@getHS')
        ->middleware($corsGroup['resourceGroup']);