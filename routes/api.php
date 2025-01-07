<?php

use App\Http\Controllers\DriverController;
use App\Http\Controllers\HitchhikerController;
use App\Http\Controllers\MatchingController;
use App\Http\Controllers\RideController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VechicleController;

Route::prefix('/user')->group(function () {
    Route::post('/', [UserController::class, 'register']);
    Route::post('/verify', [UserController::class, 'verify']);
    Route::post('/resend', [UserController::class, 'resend'])->middleware('auth:sanctum');

    Route::put('/set-password', [UserController::class, 'setPassword'])->middleware('auth:sanctum');
    Route::put('/change-password', [UserController::class, 'changePassword'])->middleware('auth:sanctum');
    Route::put('/profile', [UserController::class, 'completeYourProfile'])->middleware('auth:sanctum');
    Route::put('/role', [UserController::class, 'setRole'])->middleware('auth:sanctum');

    Route::delete('/delete', [UserController::class, 'destroy'])->middleware('auth:sanctum');

    Route::post('/login', [UserController::class, 'login']);
    Route::post('/sendOtp', [UserController::class, 'sendOtp']);

    Route::post('/logout', [UserController::class, 'logout'])->middleware('auth:sanctum');

    Route::get('/', [UserController::class, 'getUser'])->middleware('auth:sanctum');
    Route::get('/now', [UserController::class, 'getLoggedInUser'])->middleware('auth:sanctum');
});

Route::prefix('/vehicle')->group(function () {
    Route::post('/license-detail', [VechicleController::class, 'addLicenseDetails'])->middleware('auth:sanctum');
    Route::post('/vehicle-info', [VechicleController::class, 'vehicleInfo'])->middleware('auth:sanctum');
    Route::post('/supporting-docs', [VechicleController::class, 'supportingDocs'])->middleware('auth:sanctum');
});

Route::prefix('/driver')->group(function () {
    Route::get('/', [DriverController::class, 'getDriver']);
    Route::get('/rides', [DriverController::class, 'rides']);
    Route::post('/store-driver', [DriverController::class, 'storeDriver'])->middleware('auth:sanctum');
});

Route::prefix('/rides')->group(function () {
    Route::get('/', [RideController::class, 'rides']);
    Route::get('/completed-rides', [RideController::class, 'completedRides'])->middleware('auth:sanctum');
    Route::post('/store-rides', [RideController::class, 'storeRides'])->middleware('auth:sanctum');
    Route::post('/update-ride', [RideController::class, 'updateRide'])->middleware('auth:sanctum');
});

Route::prefix('/hitchhiker')->group(function () {
    Route::get('/', [HitchhikerController::class, 'getHitchhiker']);
    Route::post('/store-hitchhiker', [HitchhikerController::class, 'storeHitchhiker'])->middleware('auth:sanctum');
    Route::post('/add-rating/{id}', [HitchhikerController::class, 'addRating'])->middleware('auth:sanctum');
});

Route::prefix('/match')->group(function () {
    Route::get('/', [MatchingController::class, 'matchHitchhikersAndDrivers']);
});