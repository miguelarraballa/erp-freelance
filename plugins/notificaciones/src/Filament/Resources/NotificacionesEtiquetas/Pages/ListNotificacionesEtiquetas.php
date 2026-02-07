<?php

namespace Notificaciones\Filament\Resources\NotificacionesEtiquetas\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Notificaciones\Filament\Resources\NotificacionesEtiquetas\NotificacionEtiquetaResource;

class ListNotificacionesEtiquetas extends ListRecords
{
    protected static string $resource = NotificacionEtiquetaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
