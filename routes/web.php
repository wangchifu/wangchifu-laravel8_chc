<?php

use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\LoginController;
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

Route::get('/', function () {
    return view('index');
})->name('index');
/*
Route::middleware(['auth:sanctum', 'verified'])->get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');
*/

//gsuite登入
Route::get('login', [LoginController::class, 'login'])->name('login');
Route::post('g_auth', [LoginController::class, 'g_auth'])->name('g_auth');
//openid登入
Route::get('openid_get', [LoginController::class, 'openid_get'])->name('openid_get');
//cloudschool登入
Route::post('cloudschool_auth', [LoginController::class, 'cloudschool_auth'])->name('cloudschool_auth');
Route::get('cloudschool_back', [LoginController::class, 'cloudschool_back'])->name('cloudschool_back');
//登出
Route::post('logout', [LoginController::class, 'logout'])->name('logout');
