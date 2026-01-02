<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FacturaPdfController;

Route::middleware(['web','auth']) // ajusta si usas otro guard
    ->get('/facturas/{factura}/pdf', [FacturaPdfController::class, 'show'])
    ->name('facturas.pdf');

Route::get('/', function () {
    return view('welcome');
});
