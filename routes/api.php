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
/*
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

 */
//====================================================================================================
// ENDPOINTS Kurs
//====================================================================================================
// GET /kurs/2019-09-02 => ambil data kurs yg valid per tanggal tsb
Route::get('/kurs/{tanggal}', 'KursController@showValidKursOnDate')
        ->where('tanggal', '^[\d-]{4,}$')
        ;

// GET /kurs    => ambil data kurs (collection), bisa handle query
Route::get('/kurs', 'KursController@index')
        ;

// GET /kurs/2  => ambil data kurs brdsrkn id
Route::get('/kurs/{id}', 'KursController@show')
        ;

// POST /kurs   => tambah data kurs {kode_valas, kurs_idr, jenis, tanggal_awal, tanggal_akhir}
Route::post('/kurs', 'KursController@store')
        ->middleware( 'role:PDTT,CONSOLE');

// PUT /kurs/{id}       => update/replace data kurs id {id} dengan format {kode_valas, kurs_idr, jenis, tanggal_awal, tanggal_akhir}
Route::put('/kurs/{id}', 'KursController@update')
        ->middleware( 'role:PDTT,CONSOLE');

// DELETE /kurs/{id}    => delete data kurs id {id}
Route::delete('/kurs/{id}', 'KursController@destroy')
        ->middleware( 'role:CONSOLE,PDTT');

// POST /kurs/bkf        => update data kurs ambil dari BKF
Route::post('/kurs/bkf', 'KursController@pullFromBKF')
        ->middleware( 'role');

// GET /kurs/bkf        => ambil data kurs dari bkf
Route::get('/kurs/bkf', 'KursController@getFromBkf')
        ;

//====================================================================================================
// ENDPOINTS PENUMPANG
// Data penumpang adalah data rahasia, jd kasih guard di endpointsnya
// asal tokennya dari user yg valid dan aktif, gk masalah
//====================================================================================================

// GET /penumpang => list data penumpang dgn paginasi + query
Route::get('/penumpang', 'PenumpangController@index')
        ->middleware( 'role');

// GET /penumpang/3     => tampilkan data penumpang dgn id 3
Route::get('/penumpang/{id}', 'PenumpangController@show')
        ->middleware( 'role');

// POST /penumpang      => insert data penumpang
Route::post('/penumpang', 'PenumpangController@store')
        ->middleware( 'role');

// PUT /penumpang/3     => update/insert data penumpang dgn id 3
Route::put('/penumpang/{id}', 'PenumpangController@update')
        ->middleware( 'role');

//====================================================================================================
// ENDPOINTS CD
// CD itu classified, jd kasih guard di api endpointsnya
//====================================================================================================
// CD subresource dari dokumens
Route::get('/cd', 'CDController@index')
        ->middleware( 'role');

// GET /cd/2  => ambil data cd + relasinya
Route::get('/cd/{id}', 'CDController@show')
        ->middleware( 'role');

// POST /cd     => store data cd baru
Route::post('/cd', 'CDController@store')
        ->middleware( 'role:PDTT,CONSOLE');

// PUT /cd/{id} => update data cd
Route::put('/cd/{id}', 'CDController@update')
        ->middleware( 'role:PDTT,CONSOLE');

// DELETE /cd/{id} => hapus data cd
Route::delete('/cd/{id}', 'CDController@destroy')
        ->middleware( 'role:PDTT,CONSOLE,KASI');

// DELETE /cd/{id}/lock => buka data cd
Route::delete('/cd/{id}/lock', 'CDController@unlockCD')
        ->middleware('role:PDTT,CONSOLE,KASI');

// GET /cd/{id}/simulasi
Route::get('/cd/{id}/simulasi', 'CDController@simulasiHitung')
        ->middleware( 'role');

// PUT /cd/{id}/penetapan
Route::put('/cd/{id}/penetapan', 'CDController@storePenetapan')
        ->middleware( 'role:PDTT,CONSOLE');

// PUT /cd/{id}/bppm
Route::put('/cd/{id}/bppm', 'CDController@storeBppm')
        ->middleware( 'role:PDTT,CONSOLE');

// POST /cd/{id}/billing
Route::post('/cd/{id}/billing', 'CDController@storeBilling')
        ->middleware( 'role:PDTT,CONSOLE');

// PUT /cd/{id}/sppb
Route::put('/cd/{id}/sppb', 'CDController@storeSppb')
        ->middleware( 'role:PDTT,CONSOLE');

//==== DETAIL CD ====================================================
// GET /cd/2/details    => ambil data detail cd
Route::get('/cd/{id}/details', 'CDController@showDetails')
        ->middleware( 'role');

// GET /cd/details/{id} => ambil data detail cd spesifik
Route::get('/cd/details/{id}', 'DetailCDController@show')
        ->middleware( 'role');

// POST /cd/2/details   => tambah detail cd
Route::post('/cd/{id}/details', 'DetailCDController@store')
        ->middleware( 'role:PDTT,KASI,CONSOLE');

// PUT /cd/details/32   => update detail cd
Route::put('/cd/details/{id}', 'DetailCDController@update')
        ->middleware( 'role:PDTT,KASI,CONSOLE');

// DELETE /cd/details/32        => hapus detail cd
Route::delete('/cd/details/{id}', 'DetailCDController@destroy')
        ->middleware( 'role:PDTT,KASI,CONSOLE');

//====================================================================================================
// ENDPOINTS SPP
// SPP itu classified, jd kasih guard di api endpointsnya
//====================================================================================================
// GET /spp
Route::get('/spp', 'SPPController@index')
        ->middleware( 'role');

// GET /spp/2  => ambil data cd + relasinya
Route::get('/spp/{id}', 'SPPController@show')
        ->middleware( 'role');

// GET /cd/2/spp        => ambil data spp by cd
Route::get('/cd/{id}/spp', 'SPPController@showByCD')
        ->middleware( 'role');

// POST /cd/2/spp     => store data cd baru
Route::put('/cd/{id}/spp', 'SPPController@store')
        ->middleware( 'role:PDTT,CONSOLE');

// GET /cd/2/spp_mockup
Route::get('/cd/{id}/spp_mockup', 'SPPController@generateMockup')
        -> middleware( 'role');

// PUT /cd/{id} => update data cd
/* Route::put('/spp/{id}', 'CDController@update')
        ->middleware( 'role:PDTT,CONSOLE'); */

// DELETE /cd/{id} => hapus data cd
Route::delete('/spp/{id}', 'SPPController@destroy')
        ->middleware( 'role:PDTT,CONSOLE,KASI');

// PUT /spp/{id}/pibk   => terbitkan PIBK dari SPP
Route::put('/spp/{id}/pibk', 'SPPController@storePIBK')
        ->middleware('role:PDTT,CONSOLE');

//====================================================================================================
// ENDPOINTS ST
// ST itu classified, jd kasih guard di api endpointsnya
//====================================================================================================
// GET /st
Route::get('/st', 'STController@index')
        ->middleware( 'role');

// GET /spp/2  => ambil data cd + relasinya
Route::get('/st/{id}', 'STController@show')
        ->middleware( 'role');

// GET /cd/2/spp        => ambil data spp by cd
Route::get('/cd/{id}/st', 'STController@showByCD')
        ->middleware( 'role');

// POST /cd/2/spp     => store data cd baru
Route::put('/cd/{id}/st', 'STController@store')
        ->middleware( 'role:PDTT,CONSOLE');

// GET /cd/2/spp_mockup
Route::get('/cd/{id}/st_mockup', 'STController@generateMockup')
        -> middleware( 'role');

// PUT /cd/{id} => update data cd
/* Route::put('/spp/{id}', 'CDController@update')
        ->middleware( 'role:PDTT,CONSOLE'); */

// DELETE /cd/{id} => hapus data cd
Route::delete('/st/{id}', 'STController@destroy')
        ->middleware( 'role:PDTT,CONSOLE,KASI');

// PUT /spp/{id}/pibk   => terbitkan PIBK dari SPP
Route::put('/st/{id}/pibk', 'STController@storePIBK')
        ->middleware('role:PDTT,CONSOLE');

//====================================================================================================
// ENDPOINTS untuk data referensi umum (negara, satuan, kemasan, hs)
//====================================================================================================
// GET /negara
Route::get('/negara', 'ReferensiController@getAllNegara')
        ;

// GET /negara/id
Route::get('/negara/{kode}', 'ReferensiController@getNegaraByCode')
        ;

// POST /negara
Route::post('/negara', 'ReferensiController@storeNegara')
        ->middleware( 'role');

// GET /hs
Route::get('/hs', 'ReferensiController@getHS')
        ;

// GET /hs/{id}
Route::get('/hs/{id}', 'ReferensiController@getHSById')
        ;

// GET /kategori
Route::get('/kategori', 'ReferensiController@getKategori')
        ;

// POST /kategori
Route::post('/kategori', 'ReferensiController@storeKategori')
        ->middleware( 'role');

// GET /pelabuhan
Route::get('/pelabuhan', 'ReferensiController@getPelabuhan')
        ;

// GET /pelabuhan/{kode}
Route::get('/pelabuhan/{kode}', 'ReferensiController@getPelabuhanByKode')
        ;

// GET /kemasan
Route::get('/kemasan', 'ReferensiController@getKemasan')
        ;

// GET /kemasan/{kode}
Route::get('/kemasan/{kode}', 'ReferensiController@getKemasanByKode')
        ;

// GET /satuan
Route::get('/satuan', 'ReferensiController@getSatuan')
        ;

// GET /satuan/{kode}
Route::get('/satuan/{kode}', 'ReferensiController@getSatuanByKode')
        ;

// GET /jenis-detail-sekunder
Route::get('/jenis-detail-sekunder', 'ReferensiController@getJenisDetailSekunder')
        ;

// GET /airline
Route::get('/airline', 'ReferensiController@getAllAirline')
        ;

// Get /airline/{kode}
Route::get('/airline/{kode}', 'ReferensiController@getAirlineByKode')
        ;

// GET /dokkap
Route::get('/dokkap', 'ReferensiController@getJenisDokkap')
        ;

// GET /jenis-pungutan
Route::get('/jenis-pungutan', 'ReferensiController@getJenisPungutan')
        ;

// GET /lokasi
Route::get('/lokasi', 'ReferensiController@getLokasi')
        ;

// GET /tps
Route::get('/tps', 'ReferensiController@getTps')
        ;

// GET /pjt
Route::get('/pjt', 'ReferensiController@getPjt')
        ;

// POST /pjt
Route::post('/pjt', 'ReferensiController@storePjt')
        ->middleware( 'role');

// GET /gudang
Route::get('/gudang', 'ReferensiController@getGudang')
        ;

// POST /tps/{id}/gudang
Route::post('/tps/{id}/gudang', 'ReferensiController@storeGudang')
        ->middleware( 'role');


//====================================================================================================
// ENDPOINTS BPJ
// BPJ itu classified, jd kasih guard di api endpointsnya
//====================================================================================================
// GET /bpj
Route::get('/bpj', 'BPJController@index')
        ->middleware( 'role');

// GET /bpj/{id}
Route::get('/bpj/{id}', 'BPJController@show')
        ->middleware( 'role');

// POST /bpj
Route::post('/bpj', 'BPJController@store')
        ->middleware( 'role');

// PUT /bpj/{id}
Route::put('/bpj/{id}', 'BPJController@update')
        ->middleware( 'role');

// DELETE /bpj/{id}
Route::delete('/bpj/{id}', 'BPJController@destroy')
        ->middleware( 'role:PDTT,KASI,CONSOLE');


//====================================================================================================
// ENDPOINTS PDF
//====================================================================================================
// GET /pdf?doc=lembarhitungcd&id=2
Route::get('/pdf', 'PDFController@show')
        ;

//====================================================================================================
// ENDPOINTS FILE UPLOADS
// Perlu diguard agar tidak masuk sepam
//====================================================================================================
// POST /{doctype}/{id}/lampiran
Route::post('/{doctype}/{id}/lampiran', 'UploadController@handleUpload')
        ->middleware( 'role');

// GET /{doctype}/{id}/lampiran
Route::get('/{doctype}/{id}/lampiran', 'UploadController@getAttachments')
        ->middleware( 'role');

// GET /lampiran/{id}   -> get specific attachments
Route::get('/lampiran/{id}', 'UploadController@showAttachment')
        ->middleware( 'role');

// DELETE /lampiran/{id}-> delete specific attachment
Route::delete('/lampiran/{id}', 'UploadController@deleteAttachment')
        ->middleware( 'role');

//====================================================================================================
// ENDPOINTS PEMBATALAN
//====================================================================================================
// GET /pembatalan      -> list pembatalan
Route::get('/pembatalan', 'PembatalanController@index')
        ->middleware( 'role');

// GET /pembatalan/:id  -> lihat isi pembatalan
Route::get('/pembatalan/{id}', 'PembatalanController@show')
        ->middleware( 'role');

// POST /pembatalan     -> rekam pembatalan
Route::post('/pembatalan', 'PembatalanController@store')
        ->middleware( 'role:KASI,CONSOLE');

// PUT /pembatalan/:id  -> update data pembatalan
Route::put('/pembatalan/{id}', 'PembatalanController@update')
        ->middleware( 'role:KASI,CONSOLE');

// PUT /pembatalan/:id/:doctype/:docid  -> rekam pembatalan dokumen menggunakan pembatalan id tertentu
Route::put('/pembatalan/{id}/{doctype}/{docid}', 'PembatalanController@addDokumen')
        ->middleware( 'role:KASI,CONSOLE');

// DELETE /pembatalan/:id       -> hapus surat pembatalan
Route::delete('/pembatalan/{id}', 'PembatalanController@destroy')
        ->middleware( 'role:KASI,CONSOLE');

// DELETE /pembatalan/detail/:id        -> hapus pembatalan dokumen dgn detil id pembatalan tertentu
Route::delete('/pembatalan/detail/{id}', 'PembatalanController@delDokumen')
        ->middleware( 'role:KASI,CONSOLE');

// PUT /pembatalan/:id/lock     -> kunci dokumen pembatalan
Route::put('/pembatalan/{id}/lock', 'PembatalanController@lockPembatalan')
        ->middleware( 'role:KASI,CONSOLE');

//====================================================================================================
// ENDPOINTS SPMB
// kasih guard di endpointnya
//====================================================================================================
// GET /spmb?q=&from=&to=       -> list data spmb
Route::get('/spmb', 'SPMBController@index')
        ;


//====================================================================================================
// ENDPOINTS IP
// kasih guard di endpointnya
//====================================================================================================
Route::get('/ip', 'IPController@index')
->middleware( 'role:PDTT,PABEAN,KASI,PEMERIKSA,CONSOLE');

Route::get('/ip/{id}', 'IPController@show')
->middleware( 'role:PEMERIKSA,PDTT,KASI,PABEAN,CONSOLE');

Route::put('/{doctype}/{id}/ip', 'IPController@store')
->middleware( 'role:PDTT,PABEAN,KASI,CONSOLE');

//====================================================================================================
// ENDPOINTS LHP
// kasih guard di endpointnya
//====================================================================================================
Route::get('/spmb/{id}/lhp/berangkat', 'LHPController@showResolvedLHP')
->middleware( 'role');

Route::get('/{doctype}/{id}/lhp', 'LHPController@showResolvedLHP')
->middleware( 'role');

Route::get('/lhp/{id}', 'LHPController@showLHP')
->middleware( 'role');

Route::put('/{doctype}/{id}/lhp', 'LHPController@updateResolvedLHP')
->middleware( 'role:PEMERIKSA,CONSOLE');

//====================================================================================================
// ENDPOINTS PEMERIKSA
// kasih guard di endpointnya
//====================================================================================================
Route::get('/pemeriksa', 'ReferensiController@getPemeriksa')
->middleware( 'role');


//====================================================================================================
// ENDPOINTS EXCEL
// kasih guard di endpointnya
//====================================================================================================
Route::get('/excel/kurs', 'ExcelController@exportKurs')
->middleware( 'role');
Route::get('/excel/kurs/bkf', 'ExcelController@exportKursBkf');
Route::get('/excel/kurs/{tanggal}', 'ExcelController@exportKurs');
Route::post('/excel/kurs', 'ExcelController@importKurs');

Route::get('/excel/bppm', 'ExcelController@exportBppm')
->middleware('role');

Route::post('/excel/billing', 'ExcelController@importBilling')
->middleware('role:PERBENDAHARAAN');

Route::post('/excel/pibk', 'ExcelController@importPIBK');


//====================================================================================================
// ENDPOINTS SSOUserCache
// kasih guard di endpointnya
//====================================================================================================
Route::get('/sso/user', 'SSOUserCacheController@index')
;
Route::get('/sso/user/{id}', 'SSOUserCacheController@show')
;

//====================================================================================================
// ENDPOINTS DetailBarang (GENERALIZED)
// kasih guard di endpointnya
//====================================================================================================
Route::get('/penetapan', 'DetailBarangController@index')
->middleware('role');

Route::get('/detailbarang/{id}', 'DetailBarangController@showDetailBarang')
->middleware( 'role');

Route::get('/penetapan/{id}', 'DetailBarangController@showPenetapan')
->middleware( 'role');

Route::get('/{doctype}/{id}/penetapan', 'DetailBarangController@indexPenetapan')
->middleware( 'role');

Route::get('/{doctype}/{id}/detailbarang', 'DetailBarangController@indexDetailBarang')
->middleware( 'role');

Route::post('/{doctype}/{id}/penetapan', 'DetailBarangController@storePenetapan')
->middleware( 'role:PDTT,CONSOLE,PELAKSANA_ADMINISTRASI');

Route::put('/penetapan/{id}', 'DetailBarangController@updatePenetapan')
->middleware( 'role:PDTT,CONSOLE,PELAKSANA_ADMINISTRASI');

Route::delete('/penetapan/{id}', 'DetailBarangController@deletePenetapan')
->middleware( 'role:PDTT,CONSOLE');

//====================================================================================================
// ENDPOINTS PIBK
// kasih guard di endpointnya
//====================================================================================================
Route::get('/pibk', 'PIBKController@index')
->middleware( 'role');

Route::get('/pibk/{id}', 'PIBKController@show')
->middleware( 'role');

Route::get('/pibk/{id}/details', 'PIBKController@showDetails')
->middleware('role');

Route::post('/pibk', 'PIBKController@store')
->middleware('role:PDTT,CONSOLE,PELAKSANA_ADMINISTRASI');

Route::put('/pibk/{id}', 'PIBKController@update')
->middleware('role:PDTT,CONSOLE,PELAKSANA_ADMINISTRASI');

Route::delete('/pibk/{id}', 'PIBKController@destroy')
->middleware('role:PDTT,CONSOLE,PELAKSANA_ADMINISTRASI');

Route::get('/pibk/{id}/simulasi', 'PIBKController@simulasiHitung')
->middleware('role');

Route::put('/pibk/{id}/penetapan', 'PIBKController@storePenetapan')
->middleware('role:PDTT,CONSOLE');

Route::put('/pibk/{id}/bppm', 'PIBKController@storeBppm')
->middleware('role:PDTT,CONSOLE');

Route::post('/pibk/{id}/billing', 'PIBKController@storeBilling')
->middleware('role:PDTT,CONSOLE');

Route::put('/pibk/{id}/sppb', 'PIBKController@storeSppb')
->middleware('role:PDTT,CONSOLE');

//====================================================================================================
// ENDPOINTS BPPM
// kasih guard di endpointnya
//====================================================================================================
Route::get('/bppm', 'BPPMController@index')
->middleware('role');
