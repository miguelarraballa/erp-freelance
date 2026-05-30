<?php

namespace Servidores;

use Illuminate\Support\ServiceProvider;
use Servidores\Console\Commands\RenovacionAlertasCommand;

class ServidoresServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        if (! config('plugins.servidores', true)) {
            return;
        }

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                RenovacionAlertasCommand::class,
            ]);
        }
    }
}
