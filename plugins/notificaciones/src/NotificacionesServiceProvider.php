<?php

namespace Notificaciones;

use Illuminate\Support\ServiceProvider;
use Notificaciones\Console\Commands\EnviarNotificacionesCommand;
use Notificaciones\Console\Commands\RecordarFacturasVencidasCommand;

class NotificacionesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/notificaciones.php', 'notificaciones');
    }

    public function boot(): void
    {
        if (!config('plugins.notificaciones', true)) {
            return;
        }

        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->publishes([
            __DIR__ . '/../config/notificaciones.php' => config_path('notificaciones.php'),
        ], 'notificaciones-config');

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                EnviarNotificacionesCommand::class,
                RecordarFacturasVencidasCommand::class,
            ]);
        }
    }
}
