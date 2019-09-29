<?php

use App\Http\Controllers\ApiController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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

$corsGroup = [
    'readOnly'  => 'cors:GET,OPTIONS',
    'singleItem'=> 'cors:GET,PUT,DELETE,OPTIONS,PATCH',
    'all'       => 'cors:*',
    'resourceGroup'  => 'cors:GET,POST,OPTIONS'
];

// Kayaknya bagusnya digroup per endpoints dah

//====================================================================================================
// ENDPOINTS Kurs
//====================================================================================================
// GET /kurs/2019-09-02 => ambil data kurs yg valid per tanggal tsb
Route::get('/kurs/{tanggal}', 'KursController@showValidKursOnDate')
        ->where('tanggal', '^\d{4}\-\d{2}\-\d{2}$')
        ->middleware($corsGroup['readOnly']);

// GET /kurs    => ambil data kurs (collection), bisa handle query
Route::get('/kurs', 'KursController@index')
        ->middleware($corsGroup['resourceGroup']);

// GET /kurs/2  => ambil data kurs brdsrkn id
Route::get('/kurs/{id}', 'KursController@show')
        ->middleware($corsGroup['singleItem']);

// POST /kurs   => tambah data kurs {kode_valas, kurs_idr, jenis, tanggal_awal, tanggal_akhir}
Route::post('/kurs', 'KursController@store')
        ->middleware($corsGroup['resourceGroup']);

// PUT /kurs/{id}       => update/replace data kurs id {id} dengan format {kode_valas, kurs_idr, jenis, tanggal_awal, tanggal_akhir}
Route::put('/kurs/{id}', 'KursController@update')
        ->middleware($corsGroup['singleItem']);


//====================================================================================================
// ENDPOINTS PENUMPANG
//====================================================================================================
// GET /penumpang => list data penumpang dgn paginasi + query
Route::get('/penumpang', 'PenumpangController@index')
        ->middleware($corsGroup['resourceGroup']);

// GET /penumpang/3     => tampilkan data penumpang dgn id 3
Route::get('/penumpang/{id}', 'PenumpangController@show')
        ->middleware($corsGroup['singleItem']);

// POST /penumpang      => insert data penumpang
Route::post('/penumpang', 'PenumpangController@store')
        ->middleware($corsGroup['resourceGroup']);

// PUT /penumpang/3     => update/insert data penumpang dgn id 3
Route::put('/penumpang/{id}', 'PenumpangController@update')
        ->middleware($corsGroup['singleItem']);

//====================================================================================================
// ENDPOINTS CD
//====================================================================================================
// CD subresource dari dokumens
// GET /dokumen/cd/2  => ambil data cd + relasinya
Route::get('/dokumen/cd/{id}', 'CDController@show')
        ->middleware($corsGroup['singleItem']);

// GET /dokumen/cd/2/details    => ambil data detail cd
Route::get('/dokumen/cd/{id}/details', 'CDController@showDetails')
        ->middleware($corsGroup['resourceGroup']);

/* // untuk resource yang cuman boleh dibaca
Route::middleware($corsGroup['readOnly'])->group(function () {

}); */

// untuk resource yang merepresentasikan sekumpulan resource
/* Route::middleware($corsGroup['resourceGroup'])->group(function () {
    
    
});


// untuk resource yang merepresentasikan single CRUD item
Route::middleware($corsGroup['singleItem'])->group(function() {
    Route::options('/dokumens/cds/{id}', function () {});
}); */