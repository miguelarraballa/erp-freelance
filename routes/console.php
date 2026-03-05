<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Importar pedidos de todas las tiendas WooCommerce activas cada hora
Schedule::command('woo:import')->hourly()->withoutOverlapping();
