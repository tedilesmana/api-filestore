<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\GatewayManager\ApplicationController;
use App\Http\Controllers\Api\GatewayManager\FeatureController;
use App\Http\Controllers\Api\GatewayManager\GatewayManagerController;
use App\Http\Controllers\Api\GatewayManager\ModuleController;
use App\Http\Controllers\Api\Hris\MasterLocationController;
use App\Http\Controllers\Api\Hris\PresensiController;
use App\Http\Controllers\Api\Hris\WorkingShiftController;
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
    Route::get('working-shift/default/{id}', [WorkingShiftController::class, 'getDefaultShift']);
    Route::resource('working-shift', WorkingShiftController::class);
    Route::resource('gateway-application', ApplicationController::class);
    Route::resource('gateway-module', ModuleController::class);
    Route::resource('gateway-feature', FeatureController::class);
    Route::post('gateway-manager/{app}/{module}/{feature}/{title}', [GatewayManagerController::class, 'proceedRequest']);
    Route::put('gateway-manager/{app}/{module}/{feature}/{title}/{action}', [GatewayManagerController::class, 'updateRequest']);
    Route::resource('gateway-manager', GatewayManagerController::class);
});
