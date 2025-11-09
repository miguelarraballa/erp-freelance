<?php

use App\Http\Controllers\Api\FacturaLogController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/facturas/{factura}/logs', [FacturaLogController::class, 'index']);
    Route::get('/facturas/{factura}/logs.csv', [FacturaLogController::class, 'csv']);
});