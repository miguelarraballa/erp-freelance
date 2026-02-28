<?php

namespace Notificaciones\Filament\Resources\Notificaciones\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Notificaciones\Filament\Resources\Notificaciones\NotificacionResource;

class ListNotificaciones extends ListRecords
{
    protected static string $resource = NotificacionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
