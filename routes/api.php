<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::middleware('auth:api')->group(function () {
    Route::get('/get-suggested', 'UserController@getSuggested');
    Route::post('/feed', 'PostController@getFeed');
    Route::get('/me', 'UserController@getUser');
    Route::post('/like', 'PostController@like');
    Route::post('/comment', 'PostController@comment');
    Route::post('/subscribe', 'UserController@subscribe');
    Route::post('/user-posts', 'UserController@userPosts');
    Route::post('/upload-post', 'PostController@uploadPost');
});

Route::group([], function () {
    Route::post('/login', 'UserController@login');
    Route::post('/register', 'UserController@register');
});

