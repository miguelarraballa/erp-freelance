<?php

namespace Woocommerce;

use Illuminate\Support\ServiceProvider;
use Woocommerce\Console\Commands\ImportarPedidosWooCommand;

class WoocommerceServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

        // Registrar siempre (no solo en consola) para que Artisan::call() funcione desde web
        $this->commands([
            ImportarPedidosWooCommand::class,
        ]);
    }
}
