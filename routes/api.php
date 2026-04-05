
<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\ServicesApiController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [AuthApiController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profil', [AuthApiController::class, 'profil']);
    Route::post('/auth/logout', [AuthApiController::class, 'logout']);
    Route::get('/services', [ServicesApiController::class, 'index']);
});
