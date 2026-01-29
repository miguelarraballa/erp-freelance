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
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
