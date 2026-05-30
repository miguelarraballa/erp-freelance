<?php

use App\Http\Controllers\Api\FacturaLogController;
use App\Http\Controllers\Api\EmailMatchController;
use App\Http\Controllers\Api\N8nServidorController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/facturas/{factura}/logs', [FacturaLogController::class, 'index']);
    Route::get('/facturas/{factura}/logs.csv', [FacturaLogController::class, 'csv']);
});

// Endpoints usados por n8n para automatizaciones
Route::post('/n8n/email-check', [EmailMatchController::class, 'check'])->middleware('throttle:10,1');
Route::post('/n8n/servidores/upsert', [N8nServidorController::class, 'upsert'])->middleware('throttle:300,1');
