<?php

namespace Gastos\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Gastos\Filament\Resources\Gastos\GastoResource;
use Gastos\Filament\Widgets\IngresosGastosMesWidget;

class GastosPlugin implements Plugin
{
    public static function make(): self
    {
        return new self();
    }

    public function getId(): string
    {
        return 'gastos';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->resources([
                GastoResource::class,
            ])
            ->widgets([
                IngresosGastosMesWidget::class,
            ]);
    }

    public function boot(Panel $panel): void
    {
    }
}
