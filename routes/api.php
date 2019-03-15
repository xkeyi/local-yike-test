<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Authorization, Content-Type, Access-Control-Allow-Headers, X-Requested-With');
header('Access-Control-Allow-Methods: *');

// Auth
Route::post('auth/register', 'AuthController@register');

// User
Route::get('user/activate', 'UserController@activate')->name('user.activate');

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('test', function () {
    return 'yike api route test';
});
