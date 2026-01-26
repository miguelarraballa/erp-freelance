<?php

namespace Proyectos\Filament\Resources\Proyectos\Pages;

use Proyectos\Filament\Resources\Proyectos\ProyectoResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListProyectos extends ListRecords
{
    protected static string $resource = ProyectoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
