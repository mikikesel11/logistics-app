<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BillOfLadingController;
use App\Http\Controllers\Api\CarrierController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\LoadBoardController;
use App\Http\Controllers\Api\LoadController;
use App\Http\Controllers\Api\LocationController;
use Illuminate\Support\Facades\Route;

Route::get('/health', HealthController::class);

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('customers', CustomerController::class);
    Route::apiResource('carriers', CarrierController::class);
    Route::apiResource('locations', LocationController::class);

    Route::apiResource('loads', LoadController::class);
    Route::patch('loads/{load}/status', [LoadController::class, 'updateStatus']);

    // Bill of Lading — generate from a load, download the PDF.
    Route::post('loads/{load}/bol', [BillOfLadingController::class, 'store']);
    Route::get('loads/{load}/bol/download', [BillOfLadingController::class, 'download']);

    Route::get('load-board', [LoadBoardController::class, 'index']);
});
