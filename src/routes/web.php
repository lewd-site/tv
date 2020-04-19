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

Route::name('common.')->group(function () {
  Route::get('/', 'CommonController@landing')->name('landing');
  Route::get('/about', 'CommonController@about')->name('about');
  Route::get('/contact', 'CommonController@contact')->name('contact');
  Route::get('/donate', 'CommonController@donate')->name('donate');
});

Route::name('auth.')->group(function () {
  Route::get('/login', 'AuthController@login')->name('login');
  Route::post('/login', 'AuthController@loginSubmit')->name('loginSubmit');

  Route::get('/register', 'AuthController@register')->name('register');
  Route::post('/register', 'AuthController@registerSubmit')->name('registerSubmit');

  Route::post('/logout', 'AuthController@logout')->name('logout')->middleware('auth');
});

Route::name('users.')->group(function () {
  Route::get('/users/{id}', 'UserController@show')->name('show');
});

Route::name('rooms.')->group(function () {
  Route::get('/create', 'RoomController@create')->name('create')->middleware('auth');
  Route::post('/create', 'RoomController@createSubmit')->name('createSubmit')->middleware('auth');

  Route::get('/rooms', 'RoomController@list')->name('list');
  Route::get('/{url}', 'RoomController@show')->name('show');
});
