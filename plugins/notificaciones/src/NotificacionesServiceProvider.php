<?php

namespace Notificaciones;

use Illuminate\Support\ServiceProvider;

class NotificacionesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/notificaciones.php', 'notificaciones');
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->publishes([
            __DIR__ . '/../config/notificaciones.php' => config_path('notificaciones.php'),
        ], 'notificaciones-config');
    }
}
