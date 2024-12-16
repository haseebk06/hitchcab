<?php

use App\Http\Controllers\DriverController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VechicleController;

Route::prefix('/user')->group(function () {
    Route::post('/', [UserController::class, 'register']);
    Route::post('/verify', [UserController::class, 'verify']);
    Route::post('/resend', [UserController::class, 'resend'])->middleware('auth:sanctum');

    Route::put('/set-password', [UserController::class, 'setPassword'])->middleware('auth:sanctum');
    Route::put('/profile', [UserController::class, 'completeYourProfile'])->middleware('auth:sanctum');
    Route::put('/role', [UserController::class, 'setRole'])->middleware('auth:sanctum');

    Route::delete('/{id}', [UserController::class, 'destroy']);

    Route::post('/login', [UserController::class, 'login']);
    Route::post('/sendOtp', [UserController::class, 'sendOtp']);

    Route::post('/logout', [UserController::class, 'logout'])->middleware('auth:sanctum');

    Route::get('/', [UserController::class, 'getUser'])->middleware('auth:sanctum');
});

Route::prefix('/vehicle')->group(function () {
    Route::post('/license-detail', [VechicleController::class, 'addLicenseDetails'])->middleware('auth:sanctum');
    Route::post('/vehicle-info', [VechicleController::class, 'vehicleInfo'])->middleware('auth:sanctum');
    Route::post('/supporting-docs', [VechicleController::class, 'supportingDocs'])->middleware('auth:sanctum');
});

Route::prefix('/driver')->group(function () {
    Route::get('/', [DriverController::class, 'getDriver']);
    Route::post('/store-driver', [DriverController::class, 'storeDriver'])->middleware('auth:sanctum');
});