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

        if ($this->app->runningInConsole()) {
            $this->commands([
                ImportarPedidosWooCommand::class,
            ]);
        }
    }
}
