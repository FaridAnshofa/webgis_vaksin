<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::get('/cc', function() {
    Artisan::call('cache:clear');
//    Artisan::call('route:cache');
    Artisan::call('config:cache');
    Artisan::call('view:clear');
    return "Cache is cleared";
});

Route::get('/','IndexController@viewDashboard')->name('viewDashboard');
Route::get('/index/{type?}/{val?}','IndexController@viewDashboard')->name('viewDashboard');
Route::post('/prosesNilaiBobot','IndexController@prosesNilaiBobot')->name('prosesNilaiBobot');
Route::get('/HitungJarakCentroid','IndexController@HitungJarakCentroid')->name('HitungJarakCentroid');



Route::get('/getDataWilayah','AjaxController@getDataWilayah')->name('getDataWilayah');
Route::get('/dataCluster','AjaxController@dataCluster')->name('dataCluster');