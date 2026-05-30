<?php

namespace Gastos;

use Illuminate\Support\ServiceProvider;

class GastosServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        if (!config('plugins.gastos', true)) {
            return;
        }

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'gastos');
    }
}
