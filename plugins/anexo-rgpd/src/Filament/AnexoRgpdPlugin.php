<?php

namespace AnexoRgpd\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use AnexoRgpd\Filament\Resources\AnexoRgpd\AnexoRgpdResource;

class AnexoRgpdPlugin implements Plugin
{
    public static function make(): self
    {
        return new self();
    }

    public function getId(): string
    {
        return 'anexo-rgpd';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            AnexoRgpdResource::class,
        ]);
    }

    public function boot(Panel $panel): void {}
}
