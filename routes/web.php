<?php

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

Route::get('/','PageController@checkdb');

Route::post('/api/searchpost','PageController@searchpost');

Route::post('/api/fetchlocation','PageController@fetchlocation');

Route::post('/api/fetchauthority','PageController@fetchauthority');

Route::get('/api/fetchuser','PageController@fetchuser');

Route::post('/api/login','PageController@login');

Route::post('/api/reportuser','PageController@reportuser');

Route::post('/api/changestatus','PageController@changestatus');

Route::get('/reset/reset','PageController@reset');

Route::get('/api/alllocation','PageController@alllocation');