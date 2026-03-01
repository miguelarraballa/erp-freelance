<?php

use Illuminate\Support\Facades\Route;
use PortalClientes\Http\Controllers\PortalEmailVerificationController;
use PortalClientes\Http\Controllers\PortalFacturaPdfController;
use PortalClientes\Http\Controllers\PortalPresupuestoPdfController;
use PortalClientes\Http\Middleware\EnsureIsCliente;

// Email change verification (no auth required - accessed via email link)
Route::middleware('web')
    ->get('/portal/verify-email-change/{token}', [PortalEmailVerificationController::class, 'verify'])
    ->name('portal.verify-email-change');

// PDF downloads (auth + cliente role required)
Route::middleware(['web', 'auth', EnsureIsCliente::class])->group(function () {
    Route::get('/portal/facturas/{factura}/pdf', [PortalFacturaPdfController::class, 'show'])
        ->name('portal.facturas.pdf');
    Route::get('/portal/presupuestos/{presupuesto}/pdf', [PortalPresupuestoPdfController::class, 'show'])
        ->name('portal.presupuestos.pdf');
});
