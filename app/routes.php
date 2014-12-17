<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get('/', 'OverallController@showAllUsers');

Route::post('/addUser', 'OverallController@addUser');

Route::post('/searchUser', 'OverallController@searchUser');

Route::get('/{personId}','PersonController@showInfo');

Route::post('/upload/{personId}', 'PersonController@importLocationLog');

Route::get('/deleteUser/{personId}', 'PersonController@deleteUser');

