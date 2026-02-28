<?php

namespace Notificaciones\Filament\Resources\NotificacionesEtiquetas\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Notificaciones\Filament\Resources\NotificacionesEtiquetas\NotificacionEtiquetaResource;

class EditNotificacionEtiqueta extends EditRecord
{
    protected static string $resource = NotificacionEtiquetaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
