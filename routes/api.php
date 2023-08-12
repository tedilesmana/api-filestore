<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Category\CategoryController;
use App\Http\Controllers\Api\Comment\CommentController;
use App\Http\Controllers\Api\FileHandler\FileHandlerController;
use App\Http\Controllers\Api\ImageStore\ImageStoreController;
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

Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login-by-whatsapp', [AuthController::class, 'loginWithWhatsApp']);
Route::post('auth/login', [AuthController::class, 'login']);
Route::post('auth/login-by-whatsapp/validate', [AuthController::class, 'generateTokenWhatsApp']);
Route::get('unauthorize', [AuthController::class, 'unauthorize']);

Route::middleware('auth:api')->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::post('upload/file/local', [FileHandlerController::class, 'uploadFileToLocal']);
    Route::get('image-store/total-bycategory', [ImageStoreController::class, 'getTotalImageByCategory']);
    Route::resource('image-store', ImageStoreController::class);
    Route::resource('comment', CommentController::class);
    Route::resource('category', CategoryController::class);
});
