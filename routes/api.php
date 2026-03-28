<?php

use App\Http\Controllers\Api\FacturaLogController;
use App\Http\Controllers\Api\EmailMatchController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/facturas/{factura}/logs', [FacturaLogController::class, 'index']);
    Route::get('/facturas/{factura}/logs.csv', [FacturaLogController::class, 'csv']);
});

// Endpoint usado por n8n para comprobar si un email/subject pertenece a un cliente
Route::post('/n8n/email-check', [EmailMatchController::class, 'check'])->middleware('throttle:10,1');