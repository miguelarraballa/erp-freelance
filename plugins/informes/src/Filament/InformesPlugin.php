<?php

namespace Informes\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Informes\Filament\Resources\InformeResource;

class InformesPlugin implements Plugin
{
    public static function make(): self
    {
        return new self();
    }

    public function getId(): string
    {
        return 'informes';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            InformeResource::class,
        ]);
    }

    public function boot(Panel $panel): void {}
}
