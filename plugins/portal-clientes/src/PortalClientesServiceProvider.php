<?php

namespace PortalClientes;

use Illuminate\Support\ServiceProvider;
use PortalClientes\Providers\PortalPanelProvider;

class PortalClientesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->register(PortalPanelProvider::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'portal-clientes');
    }
}
