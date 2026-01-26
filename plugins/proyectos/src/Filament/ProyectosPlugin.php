<?php

namespace Proyectos\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Proyectos\Filament\Resources\Proyectos\ProyectoResource;
use Proyectos\Filament\Resources\ProyectoTareas\ProyectoTareaResource;

class ProyectosPlugin implements Plugin
{
    public static function make(): self
    {
        return new self();
    }

    public function getId(): string
    {
        return 'proyectos';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            ProyectoResource::class,
            ProyectoTareaResource::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
    }
}
