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

Route::middleware(['auth.apikey'])->prefix('v1')->group(function () {
    Route::post('/auth/login', [App\Http\Controllers\Api\Auth\ApiAuthController::class, 'login']);

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/auth/logout', [App\Http\Controllers\Api\Auth\ApiAuthController::class, 'logoutAllUserDevices']);

        Route::middleware(['abilities:auth:admin'])->group(function () {
            Route::post('/account/generate', [App\Http\Controllers\AccountController::class, 'generate']);
            Route::post('/accounts/transfer', [App\Http\Controllers\AccountController::class, 'transfer']);
            Route::get('/accounts/{account}/balance', [App\Http\Controllers\AccountController::class, 'balance']);
            Route::get('/accounts/{account}/transactions', [App\Http\Controllers\AccountController::class, 'transactions']);
        });
    });
});
