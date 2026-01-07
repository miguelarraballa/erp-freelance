<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FacturaPdfController;
use App\Http\Controllers\PagoJustificanteController;
use Filament\Http\Middleware\Authenticate as FilamentAuthenticate;

Route::middleware(['web', FilamentAuthenticate::class]) // usa el login de Filament
    ->group(function () {
        Route::get('/facturas/{factura}/pdf', [FacturaPdfController::class, 'show'])
            ->name('facturas.pdf');
        Route::get('/pagos/{pago}/justificante', [PagoJustificanteController::class, 'show'])
            ->name('pagos.justificante');
    });

Route::get('/', function () {
    return view('welcome');
});
