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

// Kurs API route
// GET /kurs/2  => ambil data kurs brdsrkn id
Route::get('/kurs/{id}', 'KursController@show');
// GET /kurs    => ambil data kurs (collection), bisa handle query
Route::get('/kurs', 'KursController@index');
// GET /kurs/2019-09-02 => ambil data kurs yg valid per tanggal tsb
Route::get('/kurs/{tanggal}', 'KursController@showValidKursOnDate')
        ->where('tanggal', '^\d{4}\-\d{2}\-\d{2}$');


// CD API route
// CD subresource dari dokumens
Route::get('/dokumens/cds/{id}', 'CDController@show');