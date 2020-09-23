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
Route::post('/order', 'Api\OrderController@order')->middleware('auth:api');

// Route::post('/login', 'Api\AuthController@login')->middleware('throttle:5,5');
Route::post('/login', 'Api\AuthController@login')->middleware('GrahamCampbell\Throttle\Http\Middleware\ThrottleMiddleware:5,5');

Route::get('/verify-email/{code}', 'Api\AuthController@verifyEmail')->name('verify.email');

Route::post('/register', 'Api\AuthController@register');
