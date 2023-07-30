<?php

use App\Http\Controllers\Api\AdditionalMenu\AdditionalMenuController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Comment\CommentController;
use App\Http\Controllers\Api\FileHandler\FileHandlerController;
use App\Http\Controllers\Api\GatewayManager\ApplicationController;
use App\Http\Controllers\Api\GatewayManager\FeatureController;
use App\Http\Controllers\Api\GatewayManager\GatewayManagerController;
use App\Http\Controllers\Api\GatewayManager\ModuleController;
use App\Http\Controllers\Api\ImageStore\ImageStoreController;
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


Route::get('/', function () {
    return view('welcome');
});

Route::post('upload/file/local', [FileHandlerController::class, 'uploadFileToLocal']);
Route::resource('image-store', ImageStoreController::class);
Route::resource('comment', CommentController::class);
