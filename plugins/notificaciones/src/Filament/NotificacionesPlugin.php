<?php

namespace Notificaciones\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Notificaciones\Filament\Resources\Notificaciones\NotificacionResource;
use Notificaciones\Filament\Resources\NotificacionesEtiquetas\NotificacionEtiquetaResource;
use Notificaciones\Filament\Resources\NotificacionesPlantillas\NotificacionPlantillaResource;

class NotificacionesPlugin implements Plugin
{
    public static function make(): self
    {
        return new self();
    }

    public function getId(): string
    {
        return 'notificaciones';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            NotificacionEtiquetaResource::class,
            NotificacionPlantillaResource::class,
            NotificacionResource::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
    }
}
