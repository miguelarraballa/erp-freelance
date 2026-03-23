<?php

namespace AnexoRgpd;

use Illuminate\Support\ServiceProvider;

class AnexoRgpdServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'anexo-rgpd');
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
    }
}
