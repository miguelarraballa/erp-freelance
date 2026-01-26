<?php

namespace Notificaciones\Filament\Resources\NotificacionesPlantillas\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Notificaciones\Filament\Resources\NotificacionesPlantillas\NotificacionPlantillaResource;

class ListNotificacionesPlantillas extends ListRecords
{
    protected static string $resource = NotificacionPlantillaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
