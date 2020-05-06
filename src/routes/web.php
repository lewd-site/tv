<?php

use Illuminate\Auth\GenericUser;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Http\Request;
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

Route::post('/broadcasting/auth', function (Request $request) {
  if (!auth()->check()) {
    $user = new GenericUser([
      'id'   => -mt_rand(),
      'name' => 'Anonymous',
    ]);

    $request->setUserResolver(fn () => $user);
  }

  return Broadcast::auth($request);
});

Route::name('common.')->group(function () {
  Route::get('api/time', 'CommonController@time')->name('time');
  Route::get('api/oembed', 'OEmbedController@oembed')->name('oembed');

  Route::get('', 'CommonController@landing')->name('landing');
  Route::get('about', 'CommonController@about')->name('about');
  Route::get('contact', 'CommonController@contact')->name('contact');
  Route::get('donate', 'CommonController@donate')->name('donate');
});

Route::name('auth.')->group(function () {
  Route::get('login', 'AuthController@login')->name('login');
  Route::post('login', 'AuthController@loginSubmit')->name('loginSubmit');

  Route::get('register', 'AuthController@register')->name('register');
  Route::post('register', 'AuthController@registerSubmit')->name('registerSubmit');

  Route::post('logout', 'AuthController@logout')->name('logout')->middleware('auth');
});

Route::name('users.')->group(function () {
  Route::get('users/{user}', 'UserController@show')->name('show');
});

Route::name('rooms.')->group(function () {
  Route::post('api/rooms/{room}/videos', 'RoomController@videoSubmitJson')->name('videoSubmitJson')->middleware('auth');
  Route::post('api/rooms/{room}/messages', 'RoomController@chatSubmitJson')->name('chatSubmitJson')->middleware('auth');

  Route::get('create', 'RoomController@create')->name('create')->middleware('auth');
  Route::post('create', 'RoomController@createSubmit')->name('createSubmit')->middleware('auth');

  Route::get('{room}/add-video', 'RoomController@addVideo')->name('addVideo')->middleware('auth');
  Route::post('{room}/add-video', 'RoomController@addVideoSubmit')->name('addVideoSubmit')->middleware('auth');

  Route::post('{room}/messages', 'RoomController@chatSubmit')->name('chatSubmit')->middleware('auth');

  Route::get('rooms', 'RoomController@list')->name('list');
  Route::get('{room}', 'RoomController@show')->name('show');
});

Route::name('videos.')->group(function () {
  Route::get('api/video-preview', 'VideoController@preview')->name('preview');
});
