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
Route::post('user/send-active-mail', 'UserController@sendActiveMail');
Route::get('me', 'UserController@me');
Route::post('user/reset-password', 'AuthController@reset')->name('user.reset-password');
Route::post('user/forget-password', 'AuthController@forgetPassword');
Route::post('user/mail', 'UserController@editEmail');
Route::get('user/mail', 'UserController@updateEmail')->name('user.update-email');

// Image upload
Route::post('images', 'ImageController@store');

// Resources
Route::resources([
    'users' => 'UserController',
    'nodes' => 'NodeController',
    'threads' => 'ThreadController',
]);

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('test', function () {
    return 'yike api route test';
});
