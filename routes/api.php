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

use Illuminate\Support\Facades\Route;

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

// POST /kurs/bkf        => update data kurs ambil dari BKF
Route::post('/kurs/bkf', 'KursController@pullFromBKF')
        ->middleware($corsGroup['resourceGroup'], 'role');

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
Route::get('/cd', 'CDController@index')
        ->middleware($corsGroup['resourceGroup'], 'role');

// GET /cd/2  => ambil data cd + relasinya
Route::get('/cd/{id}', 'CDController@show')
        ->middleware($corsGroup['singleItem'], 'role');

// POST /cd     => store data cd baru
Route::post('/cd', 'CDController@store')
        ->middleware($corsGroup['resourceGroup'], 'role:PDTT,CONSOLE');

// PUT /cd/{id} => update data cd
Route::put('/cd/{id}', 'CDController@update')
        ->middleware($corsGroup['singleItem'], 'role:PDTT,CONSOLE');

// DELETE /cd/{id} => hapus data cd
Route::delete('/cd/{id}', 'CDController@destroy')
        ->middleware($corsGroup['singleItem'], 'role:PDTT,CONSOLE,KASI');

// GET /cd/{id}/simulasi
Route::get('/cd/{id}/simulasi', 'CDController@simulasiHitung')
        ->middleware($corsGroup['singleItem'], 'role');

//==== DETAIL CD ====================================================
// GET /cd/2/details    => ambil data detail cd
Route::get('/cd/{id}/details', 'CDController@showDetails')
        ->middleware($corsGroup['resourceGroup'], 'role');

// GET /cd/details/{id} => ambil data detail cd spesifik
Route::get('/cd/details/{id}', 'DetailCDController@show')
        ->middleware($corsGroup['singleItem'], 'role');

// POST /cd/2/details   => tambah detail cd
Route::post('/cd/{id}/details', 'DetailCDController@store')
        ->middleware($corsGroup['resourceGroup'], 'role:PDTT,KASI,CONSOLE');

// PUT /cd/details/32   => update detail cd
Route::put('/cd/details/{id}', 'DetailCDController@update')
        ->middleware($corsGroup['singleItem'], 'role:PDTT,KASI,CONSOLE');

// DELETE /cd/details/32        => hapus detail cd
Route::delete('/cd/details/{id}', 'DetailCDController@destroy')
        ->middleware($corsGroup['singleItem'], 'role:PDTT,KASI,CONSOLE');

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
        ->middleware($corsGroup['resourceGroup'], 'role');

// GET /hs
Route::get('/hs', 'ReferensiController@getHS')
        ->middleware($corsGroup['resourceGroup']);

// GET /kategori
Route::get('/kategori', 'ReferensiController@getKategori')
        ->middleware($corsGroup['resourceGroup']);

// POST /kategori
Route::post('/kategori', 'ReferensiController@storeKategori')
        ->middleware($corsGroup['resourceGroup'], 'role');

// GET /pelabuhan
Route::get('/pelabuhan', 'ReferensiController@getPelabuhan')
        ->middleware($corsGroup['resourceGroup']);

// GET /pelabuhan/{kode}
Route::get('/pelabuhan/{kode}', 'ReferensiController@getPelabuhanByKode')
        ->middleware($corsGroup['singleItem']);

// GET /kemasan
Route::get('/kemasan', 'ReferensiController@getKemasan')
        ->middleware($corsGroup['resourceGroup']);

// GET /kemasan/{kode}
Route::get('/kemasan/{kode}', 'ReferensiController@getKemasanByKode')
        ->middleware($corsGroup['singleItem']);

// GET /satuan
Route::get('/satuan', 'ReferensiController@getSatuan')
        ->middleware($corsGroup['resourceGroup']);

// GET /satuan/{kode}
Route::get('/satuan/{kode}', 'ReferensiController@getSatuanByKode')
        ->middleware($corsGroup['singleItem']);

// GET /jenis-detail-sekunder
Route::get('/jenis-detail-sekunder', 'ReferensiController@getJenisDetailSekunder')
        ->middleware($corsGroup['resourceGroup']);

//====================================================================================================
// ENDPOINTS BPJ
// BPJ itu classified, jd kasih guard di api endpointsnya
//====================================================================================================
// GET /bpj
Route::get('/bpj', 'BPJController@index')
        ->middleware($corsGroup['resourceGroup'], 'role');

// GET /bpj/{id}
Route::get('/bpj/{id}', 'BPJController@show')
        ->middleware($corsGroup['singleItem'], 'role');

// POST /bpj
Route::post('/bpj', 'BPJController@store')
        ->middleware($corsGroup['resourceGroup'], 'role');

// PUT /bpj/{id}
Route::put('/bpj/{id}', 'BPJController@update')
        ->middleware($corsGroup['singleItem'], 'role');

// DELETE /bpj/{id}
Route::delete('/bpj/{id}', 'BPJController@destroy')
        ->middleware($corsGroup['singleItem'], 'role:PDTT,KASI,CONSOLE');
