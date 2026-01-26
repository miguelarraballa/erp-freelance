<?php

namespace Proyectos\Filament\Resources\ProyectoTareas\Pages;

use Proyectos\Filament\Resources\ProyectoTareas\ProyectoTareaResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListProyectoTareas extends ListRecords
{
    protected static string $resource = ProyectoTareaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
