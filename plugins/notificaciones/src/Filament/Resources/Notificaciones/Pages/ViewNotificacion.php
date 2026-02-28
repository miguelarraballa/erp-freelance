<?php

namespace Notificaciones\Filament\Resources\Notificaciones\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Notificaciones\Filament\Resources\Notificaciones\NotificacionResource;
use Notificaciones\Filament\Resources\Notificaciones\Schemas\NotificacionViewSchema;

class ViewNotificacion extends ViewRecord
{
    protected static string $resource = NotificacionResource::class;

    public function form(Schema $schema): Schema
    {
        return NotificacionViewSchema::configure($schema);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
