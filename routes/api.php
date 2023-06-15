<?php

use App\Http\Controllers\API\OverviewController;
use App\Http\Controllers\API\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::apiResource('setting', 'SettingController');

Route::post('/auth/register', [UserController::class, 'register']);
Route::post('/auth/login', [UserController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [UserController::class, 'fetch']);
    Route::post('/profile', [UserController::class, 'updateProfile']);
    Route::post('/auth/logout', [UserController::class, 'logout']);

    Route::apiResource('article', 'ArticleController');
    Route::apiResource('article/{article_id}/comment', 'CommentController');
    Route::apiResource('article/{article_id}/image', 'ArticleImageController');
    Route::apiResource('disease', 'DiseaseController');
    Route::apiResource('pesticide', 'PesticideController');

    // Admin Area
    Route::apiResource('users', 'UserController');
    Route::put('users/{id}/disable', [UserController::class, 'disable']);
    Route::put('users/{id}/enable', [UserController::class, 'enable']);
    Route::apiResource('plant', 'PlantController');
    Route::apiResource('plant/{plant_id}/label', 'LabelController');
    Route::apiResource('detection', 'DetectionController');
    Route::get('/overview', [OverviewController::class, 'index']);

    // Payment
    Route::apiResource('payment_method', 'PaymentMethodController');
    Route::apiResource('payment', 'PaymentController');
});
