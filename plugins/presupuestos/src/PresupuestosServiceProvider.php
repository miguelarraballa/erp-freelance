<?php

namespace Presupuestos;

use Illuminate\Support\ServiceProvider;

class PresupuestosServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/presupuestos.php', 'presupuestos');
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'presupuestos');

        $this->publishes([
            __DIR__ . '/../config/presupuestos.php' => config_path('presupuestos.php'),
        ], 'presupuestos-config');
    }
}
