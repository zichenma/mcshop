<?php

use App\Http\Controllers\Wx\BrandController;

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

# 用户模块-用户
Route::post('auth/register', 'AuthController@register');//账号注册
Route::post('auth/regCaptcha', 'AuthController@regCaptcha');//账号注册
