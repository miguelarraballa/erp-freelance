<?php

namespace Presupuestos\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Presupuestos\Filament\Pages\PresupuestosPage;

class PresupuestosPlugin implements Plugin
{
    public static function make(): self
    {
        return new self();
    }

    public function getId(): string
    {
        return 'presupuestos';
    }

    public function register(Panel $panel): void
    {
        $panel->pages([
            PresupuestosPage::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
    }
}
