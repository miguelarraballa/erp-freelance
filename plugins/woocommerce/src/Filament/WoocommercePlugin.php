<?php

namespace Woocommerce\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Woocommerce\Filament\Resources\TiendasWoo\TiendaWooResource;

class WoocommercePlugin implements Plugin
{
    public static function make(): self
    {
        return new self();
    }

    public function getId(): string
    {
        return 'woocommerce';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            TiendaWooResource::class,
        ]);
    }

    public function boot(Panel $panel): void {}
}
