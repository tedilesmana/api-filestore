<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Hris\MasterLocationController;
use App\Http\Controllers\Api\Hris\PresensiController;
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

Route::post('auth/login-by-whatsapp', [AuthController::class, 'loginWithWhatsApp']);
Route::post('auth/login-by-google', [AuthController::class, 'login']);
Route::post('auth/login-by-whatsapp/validate', [AuthController::class, 'generateTokenWhatsApp']);
Route::get('unauthorize', [AuthController::class, 'unauthorize']);
Route::resource('auth', AuthController::class);

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth:api')->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::resource('presensi', PresensiController::class);
    Route::resource('master-location', MasterLocationController::class);
});
