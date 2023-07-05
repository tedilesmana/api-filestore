<?php

use App\Http\Controllers\Api\AdditionalMenu\AdditionalMenuController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\FileHandler\FileHandlerController;
use App\Http\Controllers\Api\GatewayManager\ApplicationController;
use App\Http\Controllers\Api\GatewayManager\FeatureController;
use App\Http\Controllers\Api\GatewayManager\GatewayManagerController;
use App\Http\Controllers\Api\GatewayManager\ModuleController;
use App\Http\Controllers\Api\Hris\MasterLocationController;
use App\Http\Controllers\Api\Hris\PresensiController;
use App\Http\Controllers\Api\Hris\SettingController;
use App\Http\Controllers\Api\Hris\WorkingShiftController;
use App\Http\Controllers\Api\MasterLovGroup\MasterLovGroupController;
use App\Http\Controllers\Api\MasterLovValue\MasterLovValueController;
use App\Http\Controllers\Api\Menu\MenuController;
use App\Http\Controllers\Api\Permission\PermissionController;
use App\Http\Controllers\Api\Role\RoleController;
use App\Http\Controllers\Api\Route\RouteController;
use App\Http\Controllers\Api\Seeder\SeederDbController;
use App\Http\Controllers\Api\SubMenu\SubMenuController;
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

Route::post('seeder-insert-mhs', [SeederDbController::class, 'insertMahasiswa']);
Route::post('seeder-insert-staff', [SeederDbController::class, 'insertEmployees']);
Route::post('seeder-insert-staff-dlb', [SeederDbController::class, 'insertDlbEmployees']);

Route::post('auth/login-by-whatsapp', [AuthController::class, 'loginWithWhatsApp']);
Route::post('auth/login-by-google', [AuthController::class, 'login']);
Route::post('auth/login-by-whatsapp/validate', [AuthController::class, 'generateTokenWhatsApp']);
Route::get('unauthorize', [AuthController::class, 'unauthorize']);

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth:api')->group(function () {
    Route::get('auth/list-jabatan', [AuthController::class, 'listJabatan']);
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::resource('auth', AuthController::class);
    Route::resource('presensi', PresensiController::class);
    Route::resource('master-location', MasterLocationController::class);
    Route::get('working-shift/default/{id}', [WorkingShiftController::class, 'getDefaultShift']);
    Route::resource('working-shift', WorkingShiftController::class);
    Route::resource('gateway-application', ApplicationController::class);
    Route::resource('gateway-module', ModuleController::class);
    Route::resource('gateway-feature', FeatureController::class);
    Route::post('gateway-manager/{app}/{module}/{feature}/{title}', [GatewayManagerController::class, 'postRequest']);
    Route::get('gateway-manager/{app}/{module}/{feature}/{title}', [GatewayManagerController::class, 'getRequest']);
    Route::post('gateway-manager/{app}/{module}/{feature}/{title}/{id}', [GatewayManagerController::class, 'postRequestOneId']);
    Route::put('gateway-manager/{app}/{module}/{feature}/{title}/{id}', [GatewayManagerController::class, 'putRequestOneId']);
    Route::delete('gateway-manager/{app}/{module}/{feature}/{title}/{id}', [GatewayManagerController::class, 'deleteRequestOneId']);
    Route::get('gateway-manager/{app}/{module}/{feature}/{title}/{id}', [GatewayManagerController::class, 'getRequestOneId']);
    Route::post('gateway-manager/{app}/{module}/{feature}/{title}/{id}/{idTwo}', [GatewayManagerController::class, 'postRequestTwoId']);
    Route::put('gateway-manager/{app}/{module}/{feature}/{title}/{id}/{idTwo}', [GatewayManagerController::class, 'putRequestTwoId']);
    Route::delete('gateway-manager/{app}/{module}/{feature}/{title}/{id}/{idTwo}', [GatewayManagerController::class, 'deleteRequestTwoId']);
    Route::get('gateway-manager/{app}/{module}/{feature}/{title}/{id}/{idTwo}', [GatewayManagerController::class, 'getRequestTwoId']);
    Route::put('gateway-manager-update/{app}/{module}/{feature}/{title}/{action}', [GatewayManagerController::class, 'updateRequest']);
    Route::post('gateway-manager/add-request', [GatewayManagerController::class, 'addRequest']);
    Route::resource('gateway-manager', GatewayManagerController::class);
    Route::resource('settings', SettingController::class);
    Route::post('upload/file/local', [FileHandlerController::class, 'uploadFileToLocal']);
    Route::post('upload/file/s3', [FileHandlerController::class, 'uploadFileToS3']);
    Route::post('upload/file/resize', [FileHandlerController::class, 'uploadFileResize']);
    Route::post('upload/file/moveToS3', [FileHandlerController::class, 'moveToS3']);
    Route::resource('route', RouteController::class);
    Route::resource('menu', MenuController::class);
    Route::resource('sub-menu', SubMenuController::class);
    Route::resource('additional-menu', AdditionalMenuController::class);
    Route::resource('master-lov-value', MasterLovValueController::class);
    Route::resource('master-lov-group', MasterLovGroupController::class);
    Route::post('role/add-role-user', [RoleController::class, 'addRoleUser']);
    Route::delete('role/delete-role-user', [RoleController::class, 'deleteRoleUser']);
    Route::delete('role/delete-all-role-user', [RoleController::class, 'deleteAllRoleUser']);
    Route::resource('role', RoleController::class);
    Route::resource('permission', PermissionController::class);
});
