<?php

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

Route::post('/files/send', 'FileController@send')->name('files.send');
Route::post('/files/save', 'FileController@save')->name('files.save');
Route::get('/files/{file_name}', 'FileController@get')->name('files.get');