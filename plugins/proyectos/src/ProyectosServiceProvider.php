<?php

namespace Proyectos;

use Illuminate\Support\ServiceProvider;

class ProyectosServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        if (!config('plugins.proyectos', true)) {
            return;
        }

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
