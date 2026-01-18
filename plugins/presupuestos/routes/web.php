<?php

use Illuminate\Support\Facades\Route;
use Presupuestos\Http\Controllers\PresupuestoFacturarController;
use Presupuestos\Http\Controllers\PresupuestoPdfController;

Route::middleware(['web', 'auth'])
    ->get('/presupuestos/{presupuesto}/pdf', [PresupuestoPdfController::class, 'show'])
    ->name('presupuesto.pdf');

Route::middleware(['web', 'auth'])
    ->get('/presupuestos/{presupuesto}/facturar', [PresupuestoFacturarController::class, 'create'])
    ->name('presupuesto.facturar');
