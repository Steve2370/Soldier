
<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\ServicesApiController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [AuthApiController::class, 'login']);
Route::post('/auth/extension/echanger-code', [AuthApiController::class, 'echangerCodeExtension'])
    ->middleware('throttle:60,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profil', [AuthApiController::class, 'profil']);
    Route::post('/auth/logout', [AuthApiController::class, 'logout']);
    Route::get('/auth/oauth-data', [AuthApiController::class, 'oauthData']);
    Route::get('/services', [ServicesApiController::class, 'index']);
});
