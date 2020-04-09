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

Route::name('auth.')->group(function () {
  Route::get('/login', 'AuthController@login')->name('login');
  Route::get('/register', 'AuthController@register')->name('register');
});

Route::name('rooms.')->group(function () {
  Route::get('/', 'RoomController@list')->name('list');
  Route::get('/create', 'RoomController@create')->name('create');
  Route::get('/{id}', 'RoomController@show')->name('show');
});
