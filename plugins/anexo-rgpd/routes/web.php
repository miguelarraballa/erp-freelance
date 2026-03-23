<?php

use AnexoRgpd\Http\Controllers\AnexoRgpdPdfController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])
    ->get('/anexos-rgpd/{anexoRgpd}/pdf', [AnexoRgpdPdfController::class, 'show'])
    ->name('anexo-rgpd.pdf');
