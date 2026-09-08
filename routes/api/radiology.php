<?php

use App\Http\Controllers\Api\RadiologyController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'checkRole:radiology'])->prefix('radiology')->group(function () {
    Route::get('/orders', [RadiologyController::class, 'index']);
    Route::post('/orders/{id}/start', [RadiologyController::class, 'start']);
    Route::post('/orders/{id}/complete', [RadiologyController::class, 'complete']);
});
