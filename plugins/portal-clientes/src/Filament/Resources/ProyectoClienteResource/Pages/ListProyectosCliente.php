<?php

namespace PortalClientes\Filament\Resources\ProyectoClienteResource\Pages;

use Filament\Resources\Pages\ListRecords;
use PortalClientes\Filament\Resources\ProyectoClienteResource;

class ListProyectosCliente extends ListRecords
{
    protected static string $resource = ProyectoClienteResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
