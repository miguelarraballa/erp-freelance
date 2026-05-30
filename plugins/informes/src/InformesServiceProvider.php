<?php

namespace Informes;

use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\ServiceProvider;
use Informes\Livewire\GraficaLivewire;
use Livewire\Livewire;

class InformesServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        if (!config('plugins.informes', true)) {
            return;
        }

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'informes');

        Livewire::component('informes-grafica', GraficaLivewire::class);

        FilamentAsset::register([
            Js::make('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js'),
        ], 'contabilidad/informes');
    }
}
