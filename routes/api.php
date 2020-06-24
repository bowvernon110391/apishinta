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

// GET /kurs/bkf        => ambil data kurs dari bkf
Route::get('/kurs/bkf', 'KursController@getFromBkf')
        ->middleware($corsGroup['resourceGroup']);

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

// POST /cd/penetapan
Route::post('/cd/{id}/penetapan', 'CDController@storePenetapan')
        ->middleware($corsGroup['singleItem'], 'role:PDTT,CONSOLE');

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
// ENDPOINTS SPP
// SPP itu classified, jd kasih guard di api endpointsnya
//====================================================================================================
// GET /spp
Route::get('/spp', 'SPPController@index')
        ->middleware($corsGroup['resourceGroup'], 'role');

// GET /spp/2  => ambil data cd + relasinya
Route::get('/spp/{id}', 'SPPController@show')
        ->middleware($corsGroup['singleItem'], 'role');

// GET /cd/2/spp        => ambil data spp by cd
Route::get('/cd/{id}/spp', 'SPPController@showByCD')
        ->middleware($corsGroup['singleItem'], 'role');

// POST /cd/2/spp     => store data cd baru
Route::post('/cd/{id}/spp', 'SPPController@store')
        ->middleware($corsGroup['resourceGroup'], 'role:PDTT,CONSOLE');

// GET /cd/2/spp_mockup
Route::get('/cd/{id}/spp_mockup', 'SPPController@generateMockup')
        -> middleware($corsGroup['singleItem'], 'role');

// PUT /cd/{id} => update data cd
/* Route::put('/spp/{id}', 'CDController@update')
        ->middleware($corsGroup['singleItem'], 'role:PDTT,CONSOLE'); */

// DELETE /cd/{id} => hapus data cd
Route::delete('/spp/{id}', 'SPPController@destroy')
        ->middleware($corsGroup['singleItem'], 'role:PDTT,CONSOLE,KASI');

//====================================================================================================
// ENDPOINTS ST
// ST itu classified, jd kasih guard di api endpointsnya
//====================================================================================================
// GET /st
Route::get('/st', 'STController@index')
        ->middleware($corsGroup['resourceGroup'], 'role');

// GET /spp/2  => ambil data cd + relasinya
Route::get('/st/{id}', 'STController@show')
        ->middleware($corsGroup['singleItem'], 'role');

// GET /cd/2/spp        => ambil data spp by cd
Route::get('/cd/{id}/st', 'STController@showByCD')
        ->middleware($corsGroup['singleItem'], 'role');

// POST /cd/2/spp     => store data cd baru
Route::post('/cd/{id}/st', 'STController@store')
        ->middleware($corsGroup['resourceGroup'], 'role:PDTT,CONSOLE');

// GET /cd/2/spp_mockup
Route::get('/cd/{id}/st_mockup', 'STController@generateMockup')
        -> middleware($corsGroup['singleItem'], 'role');

// PUT /cd/{id} => update data cd
/* Route::put('/spp/{id}', 'CDController@update')
        ->middleware($corsGroup['singleItem'], 'role:PDTT,CONSOLE'); */

// DELETE /cd/{id} => hapus data cd
Route::delete('/st/{id}', 'STController@destroy')
        ->middleware($corsGroup['singleItem'], 'role:PDTT,CONSOLE,KASI');


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

// GET /hs/{id}
Route::get('/hs/{id}', 'ReferensiController@getHSById')
        ->middleware($corsGroup['singleItem']);

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

// GET /airline
Route::get('/airline', 'ReferensiController@getAllAirline')
        ->middleware($corsGroup['resourceGroup']);

// Get /airline/{kode}
Route::get('/airline/{kode}', 'ReferensiController@getAirlineByKode')
        ->middleware($corsGroup['singleItem']);

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


//====================================================================================================
// ENDPOINTS PDF
//====================================================================================================
// GET /pdf?doc=sspcp&id=2
Route::get('/pdf', 'PDFController@show')
        ->middleware($corsGroup['singleItem']);

//====================================================================================================
// ENDPOINTS FILE UPLOADS
// Perlu diguard agar tidak masuk sepam
//====================================================================================================
// POST /{doctype}/{id}/lampiran
Route::post('/{doctype}/{id}/lampiran', 'UploadController@handleUpload')
        ->middleware($corsGroup['resourceGroup'], 'role');

// GET /{doctype}/{id}/lampiran
Route::get('/{doctype}/{id}/lampiran', 'UploadController@getAttachments')
        ->middleware($corsGroup['resourceGroup'], 'role');

// GET /lampiran/{id}   -> get specific attachments
Route::get('/lampiran/{id}', 'UploadController@showAttachment')
        ->middleware($corsGroup['singleItem'], 'role');

// DELETE /lampiran/{id}-> delete specific attachment
Route::delete('/lampiran/{id}', 'UploadController@deleteAttachment')
        ->middleware($corsGroup['singleItem'], 'role');

//====================================================================================================
// ENDPOINTS PEMBATALAN
//====================================================================================================
// GET /pembatalan      -> list pembatalan
Route::get('/pembatalan', 'PembatalanController@index')
        ->middleware($corsGroup['resourceGroup'], 'role');

// GET /pembatalan/:id  -> lihat isi pembatalan
Route::get('/pembatalan/{id}', 'PembatalanController@show')
        ->middleware($corsGroup['singleItem'], 'role');

// POST /pembatalan     -> rekam pembatalan
Route::post('/pembatalan', 'PembatalanController@store')
        ->middleware($corsGroup['resourceGroup'], 'role:KASI,CONSOLE');

// PUT /pembatalan/:id  -> update data pembatalan
Route::put('/pembatalan/{id}', 'PembatalanController@update')
        ->middleware($corsGroup['singleItem'], 'role:KASI,CONSOLE');

// PUT /pembatalan/:id/:doctype/:docid  -> rekam pembatalan dokumen menggunakan pembatalan id tertentu
Route::put('/pembatalan/{id}/{doctype}/{docid}', 'PembatalanController@addDokumen')
        ->middleware($corsGroup['singleItem'], 'role:KASI,CONSOLE');

// DELETE /pembatalan/:id       -> hapus surat pembatalan
Route::delete('/pembatalan/{id}', 'PembatalanController@destroy')
        ->middleware($corsGroup['singleItem'], 'role:KASI,CONSOLE');

// DELETE /pembatalan/detail/:id        -> hapus pembatalan dokumen dgn detil id pembatalan tertentu
Route::delete('/pembatalan/detail/{id}', 'PembatalanController@delDokumen')
        ->middleware($corsGroup['singleItem'], 'role:KASI,CONSOLE');

// PUT /pembatalan/:id/lock     -> kunci dokumen pembatalan
Route::put('/pembatalan/{id}/lock', 'PembatalanController@lockPembatalan')
        ->middleware($corsGroup['singleItem'], 'role:KASI,CONSOLE');

//====================================================================================================
// ENDPOINTS SPMB
// kasih guard di endpointnya
//====================================================================================================
// GET /spmb?q=&from=&to=       -> list data spmb
Route::get('/spmb', 'SPMBController@index')
        ->middleware($corsGroup['resourceGroup']);


//====================================================================================================
// ENDPOINTS LHP
// kasih guard di endpointnya
//====================================================================================================
Route::get('/spmb/{id}/lhp/berangkat', 'LHPController@showResolvedLHP')
->middleware($corsGroup['singleItem']);

Route::get('/{doctype}/{id}/lhp', 'LHPController@showResolvedLHP')
->middleware($corsGroup['singleItem']);

Route::get('/lhp/{id}', 'LHPController@showResolvedLHP')
->middleware($corsGroup['singleItem']);

//====================================================================================================
// ENDPOINTS PEMERIKSA
// kasih guard di endpointnya
//====================================================================================================
Route::get('/pemeriksa', 'ReferensiController@getPemeriksa')
->middleware($corsGroup['resourceGroup'], 'role');


//====================================================================================================
// ENDPOINTS EXCEL
// kasih guard di endpointnya
//====================================================================================================
Route::get('/excel/kurs', 'ExcelController@exportKurs')
->middleware($corsGroup['resourceGroup'], 'role');
Route::get('/excel/kurs/bkf', 'ExcelController@exportKursBkf');
Route::get('/excel/kurs/{tanggal}', 'ExcelController@exportKurs');
Route::post('/excel/kurs', 'ExcelController@importKurs')
->middleware($corsGroup['singleItem']);


//====================================================================================================
// ENDPOINTS SSOUserCache
// kasih guard di endpointnya
//====================================================================================================
Route::get('/sso/user', 'SSOUserCacheController@index')
->middleware($corsGroup['resourceGroup']);
Route::get('/sso/user/{id}', 'SSOUserCacheController@show')
->middleware($corsGroup['singleItem']);